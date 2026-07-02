<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Tests\Factories\Parsers;

use ComponoKit\Databases\Sql\QueryBuilder\Exceptions\SqlParseException;
use ComponoKit\Databases\Sql\QueryBuilder\Parsers\LimitClauseParser;
use PHPUnit\Framework\TestCase;

class LimitClauseParserTest extends TestCase
{
	public function testSimpleLimit(): void
	{
		$limit = LimitClauseParser::parse('10', null);

		$this->assertSame(10, $limit->getCount());
		$this->assertSame(0, $limit->getOffset());
	}

	public function testLimitWithOffset(): void
	{
		$limit = LimitClauseParser::parse('10', '5');

		$this->assertSame(10, $limit->getCount());
		$this->assertSame(5, $limit->getOffset());
	}

	public function testMysqlCommaSyntax(): void
	{
		// LimitBuilder outputs LIMIT offset,count — so we re-parse it correctly
		$limit = LimitClauseParser::parse('5,10', null);

		$this->assertSame(10, $limit->getCount());
		$this->assertSame(5, $limit->getOffset());
	}

	public function testInvalidLimitThrows(): void
	{
		$this->expectException(SqlParseException::class);
		LimitClauseParser::parse('abc', null);
	}

	public function testInvalidOffsetThrows(): void
	{
		$this->expectException(SqlParseException::class);
		LimitClauseParser::parse('10', 'abc');
	}
}
