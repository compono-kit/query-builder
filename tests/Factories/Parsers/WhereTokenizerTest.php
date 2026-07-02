<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Tests\Factories\Parsers;

use ComponoKit\Databases\Sql\QueryBuilder\Exceptions\SqlParseException;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Token;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Types\TokenType;
use ComponoKit\Databases\Sql\QueryBuilder\Parsers\WhereTokenizer;
use PHPUnit\Framework\TestCase;

class WhereTokenizerTest extends TestCase
{
	public function testSimpleEquality(): void
	{
		$tokens = WhereTokenizer::tokenize("id = 1");

		$this->assertCount(3, $tokens);
		$this->assertToken($tokens[0], TokenType::COLUMN, 'id');
		$this->assertToken($tokens[1], TokenType::EQ, '=');
		$this->assertToken($tokens[2], TokenType::NUMBER_VALUE, '1');
	}

	public function testStringValue(): void
	{
		$tokens = WhereTokenizer::tokenize("name = 'foo'");

		$this->assertCount(3, $tokens);
		$this->assertToken($tokens[0], TokenType::COLUMN, 'name');
		$this->assertToken($tokens[1], TokenType::EQ, '=');
		$this->assertToken($tokens[2], TokenType::STRING_VALUE, 'foo');
	}

	public function testTablePrefixedColumn(): void
	{
		$tokens = WhereTokenizer::tokenize("users.id = 1");

		$this->assertCount(3, $tokens);
		$this->assertSame(TokenType::COLUMN, $tokens[0]->type);
		$this->assertSame('users', $tokens[0]->tablePrefix);
		$this->assertSame('id', $tokens[0]->value);
	}

	public function testPreparedParameter(): void
	{
		$tokens = WhereTokenizer::tokenize("id = :userId");

		$this->assertCount(3, $tokens);
		$this->assertToken($tokens[2], TokenType::PARAM, 'userId');
	}

	public function testLogicalOperators(): void
	{
		$tokens = WhereTokenizer::tokenize("a = 1 AND b = 2 OR c = 3");

		$this->assertSame(TokenType::AND_OP, $tokens[3]->type);
		$this->assertSame(TokenType::OR_OP, $tokens[7]->type);
	}

	public function testComparisonOperators(): void
	{
		$operators = ['>=', '<=', '!=', '<>', '>', '<'];
		$expected  = [TokenType::GEQ, TokenType::LEQ, TokenType::NEQ, TokenType::NEQ, TokenType::GT, TokenType::LT];

		foreach ($operators as $i => $op)
		{
			$tokens = WhereTokenizer::tokenize("id {$op} 1");
			$this->assertSame($expected[$i], $tokens[1]->type, "Operator: {$op}");
		}
	}

	public function testIsNullAndIsNotNull(): void
	{
		$tokensNull    = WhereTokenizer::tokenize("col IS NULL");
		$tokensNotNull = WhereTokenizer::tokenize("col IS NOT NULL");

		$this->assertSame(TokenType::IS_NULL, $tokensNull[1]->type);
		$this->assertSame(TokenType::IS_NOT_NULL, $tokensNotNull[1]->type);
	}

	public function testInAndNotIn(): void
	{
		$tokensIn    = WhereTokenizer::tokenize("id IN (1, 2, 3)");
		$tokensNotIn = WhereTokenizer::tokenize("id NOT IN (1, 2, 3)");

		$this->assertSame(TokenType::IN_OP, $tokensIn[1]->type);
		$this->assertSame(TokenType::NOT_IN, $tokensNotIn[1]->type);
	}

	public function testLikeAndNotLike(): void
	{
		$tokensLike    = WhereTokenizer::tokenize("name LIKE '%foo%'");
		$tokensNotLike = WhereTokenizer::tokenize("name NOT LIKE '%foo%'");

		$this->assertSame(TokenType::LIKE_OP, $tokensLike[1]->type);
		$this->assertSame(TokenType::NOT_LIKE, $tokensNotLike[1]->type);
	}

	public function testParentheses(): void
	{
		$tokens = WhereTokenizer::tokenize("(a = 1)");

		$this->assertSame(TokenType::OPEN_PAREN, $tokens[0]->type);
		$this->assertSame(TokenType::CLOSE_PAREN, $tokens[4]->type);
	}

	public function testUnexpectedCharacterThrows(): void
	{
		$this->expectException(SqlParseException::class);
		WhereTokenizer::tokenize("id $ 1");
	}

	private function assertToken(Token $token, TokenType $expectedType, string $expectedValue): void
	{
		$this->assertSame($expectedType, $token->type);
		$this->assertSame($expectedValue, $token->value);
	}
}
