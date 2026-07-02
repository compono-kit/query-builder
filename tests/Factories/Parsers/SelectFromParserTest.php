<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Tests\Factories\Parsers;

use ComponoKit\Databases\Sql\QueryBuilder\Exceptions\SqlParseException;
use ComponoKit\Databases\Sql\QueryBuilder\Parsers\SelectFromParser;
use PHPUnit\Framework\TestCase;

class SelectFromParserTest extends TestCase
{
	public function testDefaultsToSelectStarWhenSelectIsNull(): void
	{
		$output = SelectFromParser::parse( null, 'users' )->build();

		$this->assertSame( 'SELECT * FROM users', $output );
	}

	public function testDefaultsToSelectStarWhenSelectIsStar(): void
	{
		$output = SelectFromParser::parse( '*', 'users' )->build();

		$this->assertSame( 'SELECT * FROM users', $output );
	}

	public function testParsesSelectExpressions(): void
	{
		$output = SelectFromParser::parse( 'id, name', 'users' )->build();

		$this->assertSame( 'SELECT id, name FROM users', $output );
	}

	public function testKeepsCommasInsideFunctionCalls(): void
	{
		$output = SelectFromParser::parse( "COUNT(id), COALESCE(name, 'x')", 'users' )->build();

		$this->assertSame( "SELECT COUNT(id), COALESCE(name, 'x') FROM users", $output );
	}

	public function testParsesFromWithAlias(): void
	{
		$output = SelectFromParser::parse( '*', 'users u' )->build();

		$this->assertSame( 'SELECT * FROM users u', $output );
	}

	public function testParsesFromWithAsAlias(): void
	{
		$output = SelectFromParser::parse( '*', 'users AS u' )->build();

		$this->assertSame( 'SELECT * FROM users u', $output );
	}

	public function testThrowsWhenFromIsNull(): void
	{
		$this->expectException( SqlParseException::class );

		SelectFromParser::parse( '*', null );
	}

	public function testThrowsWhenFromIsInvalid(): void
	{
		$this->expectException( SqlParseException::class );

		SelectFromParser::parse( '*', '(SELECT id FROM users) sub' );
	}
}
