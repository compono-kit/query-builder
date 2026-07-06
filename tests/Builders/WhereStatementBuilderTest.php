<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Tests\Builders;

use ComponoKit\Databases\Sql\QueryBuilder\Builders\WhereStatementBuilder;
use PHPUnit\Framework\TestCase;

class WhereStatementBuilderTest extends TestCase
{
	public function testEmptyCriteriasReturnWhereOne(): void
	{
		$this->assertSame(' WHERE 1', ( new WhereStatementBuilder([]) )->buildWhereStatement());
	}

	public function testFromSqlParsesClause(): void
	{
		$this->assertSame(' WHERE id = 1', WhereStatementBuilder::fromSql('id = 1')->buildWhereStatement());
	}

	public function testFromSqlExtractsPreparedParameters(): void
	{
		$params = WhereStatementBuilder::fromSql('id = :id')->getPreparedParams();

		$this->assertArrayHasKey('id', $params);
	}
}
