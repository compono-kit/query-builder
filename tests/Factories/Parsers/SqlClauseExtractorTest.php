<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Tests\Factories\Parsers;

use ComponoKit\Databases\Sql\QueryBuilder\Factories\Parsers\SqlClauseExtractor;
use PHPUnit\Framework\TestCase;

class SqlClauseExtractorTest extends TestCase
{
	public function testExtractsWhereClause(): void
	{
		$result = SqlClauseExtractor::extract("SELECT * FROM users WHERE id = 1");

		$this->assertSame('id = 1', $result['where']);
		$this->assertNull($result['orderBy']);
		$this->assertNull($result['groupBy']);
		$this->assertNull($result['limit']);
		$this->assertNull($result['offset']);
	}

	public function testExtractsOrderByClause(): void
	{
		$result = SqlClauseExtractor::extract("SELECT * FROM users ORDER BY created_at DESC");

		$this->assertSame('created_at DESC', $result['orderBy']);
		$this->assertNull($result['where']);
	}

	public function testExtractsLimitAndOffset(): void
	{
		$result = SqlClauseExtractor::extract("SELECT * FROM users LIMIT 10 OFFSET 5");

		$this->assertSame('10', $result['limit']);
		$this->assertSame('5', $result['offset']);
	}

	public function testExtractsAllClauses(): void
	{
		$sql    = "SELECT * FROM users WHERE id > 0 GROUP BY department HAVING count > 1 ORDER BY name ASC LIMIT 20 OFFSET 10";
		$result = SqlClauseExtractor::extract($sql);

		$this->assertSame('id > 0', $result['where']);
		$this->assertSame('department', $result['groupBy']);
		$this->assertSame('count > 1', $result['having']);
		$this->assertSame('name ASC', $result['orderBy']);
		$this->assertSame('20', $result['limit']);
		$this->assertSame('10', $result['offset']);
	}

	public function testReturnsNullForMissingClauses(): void
	{
		$result = SqlClauseExtractor::extract("SELECT * FROM users");

		$this->assertNull($result['where']);
		$this->assertNull($result['orderBy']);
		$this->assertNull($result['groupBy']);
		$this->assertNull($result['limit']);
		$this->assertNull($result['offset']);
		$this->assertNull($result['having']);
	}

	public function testWhereWithoutFromKeyword(): void
	{
		$result = SqlClauseExtractor::extract("WHERE status = 'active' ORDER BY id");

		$this->assertSame("status = 'active'", $result['where']);
		$this->assertSame('id', $result['orderBy']);
	}
}
