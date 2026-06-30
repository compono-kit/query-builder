<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Factories\Parsers;

use ComponoKit\Databases\Sql\QueryBuilder\Criterias\Conditions\Condition;
use ComponoKit\Databases\Sql\QueryBuilder\Criterias\Conditions\InSubQueryCondition;
use ComponoKit\Databases\Sql\QueryBuilder\Criterias\Conditions\InValuesCondition;
use ComponoKit\Databases\Sql\QueryBuilder\Criterias\Conditions\IsNotNullCondition;
use ComponoKit\Databases\Sql\QueryBuilder\Criterias\Conditions\IsNullCondition;
use ComponoKit\Databases\Sql\QueryBuilder\Criterias\Conditions\NotInSubQueryCondition;
use ComponoKit\Databases\Sql\QueryBuilder\Criterias\Conditions\NotInValuesCondition;
use ComponoKit\Databases\Sql\QueryBuilder\Criterias\Criteria;
use ComponoKit\Databases\Sql\QueryBuilder\Criterias\IsolatedCriteria;
use ComponoKit\Databases\Sql\QueryBuilder\Factories\Exceptions\SqlParseException;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Column;
use ComponoKit\Databases\Sql\QueryBuilder\Models\ColumnName;
use ComponoKit\Databases\Sql\QueryBuilder\Models\ComparisonColumn;
use ComponoKit\Databases\Sql\QueryBuilder\Models\ComparisonValue;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Interfaces\RepresentsColumn;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Interfaces\RepresentsCriteria;
use ComponoKit\Databases\Sql\QueryBuilder\Models\PreparedParameter;
use ComponoKit\Databases\Sql\QueryBuilder\Models\TableName;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Types\ComparisonOperator;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Types\LogicalOperator;

class WhereClauseParser
{
	/** @var Token[] */
	private array $tokens;

	private int $position = 0;

	private function __construct(array $tokens)
	{
		$this->tokens = $tokens;
	}

	/**
	 * @throws SqlParseException
	 */
	public static function parse(string $whereClause): RepresentsCriteria
	{
		$tokens = WhereTokenizer::tokenize($whereClause);
		$parser = new self($tokens);

		return $parser->parseExpression();
	}

	private function parseExpression(): RepresentsCriteria
	{
		return $this->parseOrExpression();
	}

	private function parseOrExpression(): RepresentsCriteria
	{
		$parts = [$this->parseAndExpression()];
		$hasOr = false;

		while ($this->peek()?->type === TokenType::OR_OP)
		{
			$this->consume();
			$parts[] = $this->parseAndExpression();
			$hasOr   = true;
		}

		if (!$hasOr)
		{
			return $parts[0];
		}

		// AND groups with multiple conditions get wrapped in IsolatedCriteria so the
		// output SQL remains unambiguous (AND binds tighter than OR).
		$wrappedParts = array_map(function (RepresentsCriteria $part) {
			if ($part instanceof Criteria && $part->getLogicalOperator() === LogicalOperator::AND)
			{
				return new IsolatedCriteria(LogicalOperator::AND, ...$part->getCriterias());
			}

			return $part;
		}, $parts);

		return new Criteria(LogicalOperator::OR, ...$wrappedParts);
	}

	private function parseAndExpression(): RepresentsCriteria
	{
		$parts  = [$this->parsePrimary()];
		$hasAnd = false;

		while ($this->peek()?->type === TokenType::AND_OP)
		{
			$this->consume();
			$parts[] = $this->parsePrimary();
			$hasAnd  = true;
		}

		if (!$hasAnd)
		{
			return $parts[0];
		}

		return new Criteria(LogicalOperator::AND, ...$parts);
	}

	private function parsePrimary(): RepresentsCriteria
	{
		if ($this->peek()?->type === TokenType::OPEN_PAREN)
		{
			$this->consume();
			$inner = $this->parseOrExpression();
			$this->expect(TokenType::CLOSE_PAREN);

			if ($inner instanceof Criteria)
			{
				return new IsolatedCriteria($inner->getLogicalOperator(), ...$inner->getCriterias());
			}

			return new IsolatedCriteria(LogicalOperator::AND, $inner);
		}

		return $this->parseCondition();
	}

	private function parseCondition(): RepresentsCriteria
	{
		$columnToken = $this->expect(TokenType::COLUMN);
		$column      = $this->buildColumn($columnToken);
		$operator    = $this->expectOperator();

		return match ($operator->type)
		{
			TokenType::IS_NULL     => new IsNullCondition($column),
			TokenType::IS_NOT_NULL => new IsNotNullCondition($column),
			TokenType::IN_OP       => $this->parseInCondition($column, false),
			TokenType::NOT_IN      => $this->parseInCondition($column, true),
			default                => $this->parseComparisonCondition($column, $operator),
		};
	}

	private function parseComparisonCondition(RepresentsColumn $column, Token $operatorToken): RepresentsCriteria
	{
		$valueToken        = $this->consumeValue();
		$preparedParameter = null;
		$value             = null;

		if ($valueToken->type === TokenType::PARAM)
		{
			$preparedParameter = new PreparedParameter($valueToken->value, '');
		}
		elseif ($valueToken->type === TokenType::COLUMN)
		{
			$value = new ComparisonColumn($this->buildColumn($valueToken));
		}
		elseif ($valueToken->type === TokenType::STRING_VALUE)
		{
			$value = new ComparisonValue($valueToken->value);
		}
		else
		{
			$rawValue = $valueToken->value;
			$value    = new ComparisonValue(
				str_contains($rawValue, '.') ? (float)$rawValue : (int)$rawValue
			);
		}

		$comparisonOperator = match ($operatorToken->type)
		{
			TokenType::EQ       => ComparisonOperator::equalOperator(),
			TokenType::NEQ      => ComparisonOperator::notEqualOperator(),
			TokenType::GT       => ComparisonOperator::greaterOperator(),
			TokenType::LT       => ComparisonOperator::lessOperator(),
			TokenType::GEQ      => ComparisonOperator::equalOrGreaterOperator(),
			TokenType::LEQ      => ComparisonOperator::equalOrLessOperator(),
			TokenType::LIKE_OP  => ComparisonOperator::likeOperator(),
			TokenType::NOT_LIKE => ComparisonOperator::notLikeOperator(),
			default             => throw new SqlParseException(sprintf('Unexpected operator token: %s', $operatorToken->type->name)),
		};

		return new Condition($column, $comparisonOperator, $preparedParameter, $value);
	}

	private function parseInCondition(RepresentsColumn $column, bool $negated): RepresentsCriteria
	{
		$this->expect(TokenType::OPEN_PAREN);

		$first = $this->peek();
		if ($first !== null && $first->type === TokenType::COLUMN && strtoupper($first->value) === 'SELECT')
		{
			$subQuery = $this->collectUntilCloseParen();
			$this->expect(TokenType::CLOSE_PAREN);

			return $negated
				? new NotInSubQueryCondition($column, $subQuery)
				: new InSubQueryCondition($column, $subQuery);
		}

		$values = [$this->parseRawValue()];
		while ($this->peek()?->type === TokenType::COMMA)
		{
			$this->consume();
			$values[] = $this->parseRawValue();
		}
		$this->expect(TokenType::CLOSE_PAREN);

		return $negated
			? new NotInValuesCondition($column, $values)
			: new InValuesCondition($column, $values);
	}

	private function parseRawValue(): string|int|float
	{
		$token = $this->consume();

		return match ($token->type)
		{
			TokenType::STRING_VALUE, TokenType::COLUMN => $token->value,
			TokenType::NUMBER_VALUE                    => str_contains($token->value, '.') ? (float)$token->value : (int)$token->value,
			default                                    => throw new SqlParseException(sprintf('Expected value, got %s ("%s")', $token->type->name, $token->rawMatch)),
		};
	}

	private function collectUntilCloseParen(): string
	{
		$parts = [];
		$depth = 0;

		while ($this->position < count($this->tokens))
		{
			$token = $this->tokens[$this->position];

			if ($token->type === TokenType::OPEN_PAREN)
			{
				$depth++;
			}
			elseif ($token->type === TokenType::CLOSE_PAREN)
			{
				if ($depth === 0)
				{
					break;
				}
				$depth--;
			}
			$parts[] = $token->rawMatch;
			$this->position++;
		}

		return implode(' ', $parts);
	}

	private function buildColumn(Token $token): RepresentsColumn
	{
		return new Column(
			new TableName($token->tablePrefix ?? ''),
			new ColumnName($token->value)
		);
	}

	private function peek(): ?Token
	{
		return $this->tokens[$this->position] ?? null;
	}

	private function consume(): Token
	{
		if ($this->position >= count($this->tokens))
		{
			throw new SqlParseException('Unexpected end of input');
		}

		return $this->tokens[$this->position++];
	}

	private function expect(TokenType $type): Token
	{
		$token = $this->consume();

		if ($token->type !== $type)
		{
			throw new SqlParseException(sprintf(
				'Expected %s, got %s ("%s")',
				$type->name,
				$token->type->name,
				$token->rawMatch
			));
		}

		return $token;
	}

	private function expectOperator(): Token
	{
		$operatorTypes = [
			TokenType::EQ,
			TokenType::NEQ,
			TokenType::GT,
			TokenType::LT,
			TokenType::GEQ,
			TokenType::LEQ,
			TokenType::LIKE_OP,
			TokenType::NOT_LIKE,
			TokenType::IS_NULL,
			TokenType::IS_NOT_NULL,
			TokenType::IN_OP,
			TokenType::NOT_IN,
		];

		$token = $this->consume();

		if (!in_array($token->type, $operatorTypes, true))
		{
			throw new SqlParseException(sprintf(
				'Expected operator, got %s ("%s")',
				$token->type->name,
				$token->rawMatch
			));
		}

		return $token;
	}

	private function consumeValue(): Token
	{
		$valueTypes = [
			TokenType::PARAM,
			TokenType::STRING_VALUE,
			TokenType::NUMBER_VALUE,
			TokenType::COLUMN,
		];

		$token = $this->consume();

		if (!in_array($token->type, $valueTypes, true))
		{
			throw new SqlParseException(sprintf(
				'Expected value, got %s ("%s")',
				$token->type->name,
				$token->rawMatch
			));
		}

		return $token;
	}
}
