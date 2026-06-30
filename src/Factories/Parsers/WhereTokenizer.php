<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Factories\Parsers;

use ComponoKit\Databases\Sql\QueryBuilder\Factories\Exceptions\SqlParseException;

class WhereTokenizer
{
	/**
	 * Ordered patterns — longer/more-specific before shorter.
	 * Keywords must appear before COLUMN to avoid misidentification.
	 *
	 * @return array<array{TokenType, string}>
	 */
	private static function patterns(): array
	{
		return [
			[TokenType::OPEN_PAREN,   '/^\(/'],
			[TokenType::CLOSE_PAREN,  '/^\)/'],
			[TokenType::COMMA,        '/^,/'],
			[TokenType::IS_NOT_NULL,  '/^IS\s+NOT\s+NULL\b/i'],
			[TokenType::IS_NULL,      '/^IS\s+NULL\b/i'],
			[TokenType::NOT_IN,       '/^NOT\s+IN\b/i'],
			[TokenType::NOT_LIKE,     '/^NOT\s+LIKE\b/i'],
			[TokenType::AND_OP,       '/^AND\b/i'],
			[TokenType::OR_OP,        '/^OR\b/i'],
			[TokenType::IN_OP,        '/^IN\b/i'],
			[TokenType::LIKE_OP,      '/^LIKE\b/i'],
			[TokenType::GEQ,          '/^>=/'],
			[TokenType::LEQ,          '/^<=/'],
			[TokenType::NEQ,          '/^(?:!=|<>)/'],
			[TokenType::GT,           '/^>/'],
			[TokenType::LT,           '/^</'],
			[TokenType::EQ,           '/^=/'],
			[TokenType::PARAM,        '/^:([a-zA-Z_][a-zA-Z0-9_]*)/'],
			[TokenType::STRING_VALUE, '/^\'((?:[^\']|\'\')*)\'/'],
			[TokenType::NUMBER_VALUE, '/^-?\d+(?:\.\d+)?/'],
			[TokenType::COLUMN,       '/^([a-zA-Z_][a-zA-Z0-9_]*)(?:\.([a-zA-Z_][a-zA-Z0-9_]*))?/'],
		];
	}

	/**
	 * @return Token[]
	 * @throws SqlParseException
	 */
	public static function tokenize(string $input): array
	{
		$tokens = [];
		$pos    = 0;
		$length = strlen($input);

		while ($pos < $length)
		{
			if (ctype_space($input[$pos]))
			{
				$pos++;
				continue;
			}

			$remaining = substr($input, $pos);
			$matched   = false;

			foreach (self::patterns() as [$tokenType, $pattern])
			{
				if (!preg_match($pattern, $remaining, $matches))
				{
					continue;
				}

				$rawMatch    = $matches[0];
				$value       = $matches[1] ?? $rawMatch;
				$tablePrefix = null;

				if ($tokenType === TokenType::COLUMN && isset($matches[2]) && $matches[2] !== '')
				{
					$tablePrefix = $matches[1];
					$value       = $matches[2];
				}

				$tokens[] = new Token($tokenType, $rawMatch, $value, $tablePrefix);
				$pos      += strlen($rawMatch);
				$matched  = true;
				break;
			}

			if (!$matched)
			{
				throw new SqlParseException(sprintf('Unexpected character "%s" at position %d', $input[$pos], $pos));
			}
		}

		return $tokens;
	}
}
