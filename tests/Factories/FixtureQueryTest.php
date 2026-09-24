<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Tests\Factories;

use ComponoKit\Databases\Sql\QueryBuilder\Builders\SelectBuilder;
use ComponoKit\Databases\Sql\QueryBuilder\Factories\SqlToQueryBuilderFactory;
use ComponoKit\Databases\Sql\QueryBuilder\Parsers\SqlClauseExtractor;
use PHPUnit\Framework\TestCase;

class FixtureQueryTest extends TestCase
{
	private static function loadFixture( string $name ): string
	{
		return file_get_contents( __DIR__ . '/../fixtures/' . $name );
	}

	public function testQuery1ExtractsOnlyTopLevelWhere(): void
	{
		$clauses = SqlClauseExtractor::extract( self::loadFixture( 'query-1.sql' ) );

		$this->assertNull( $clauses->where );
	}

	public function testQuery1ExtractsOuterGroupBy(): void
	{
		$clauses = SqlClauseExtractor::extract( self::loadFixture( 'query-1.sql' ) );

		$this->assertSame( 't1.id', $clauses->groupBy );
	}

	public function testQuery1ExtractsOrderBy(): void
	{
		$clauses = SqlClauseExtractor::extract( self::loadFixture( 'query-1.sql' ) );

		$this->assertSame( 't1.createdOn, t1.logisticianId DESC', $clauses->orderBy );
	}

	public function testQuery1ExtractsNoTopLevelJoin(): void
	{
		$clauses = SqlClauseExtractor::extract( self::loadFixture( 'query-1.sql' ) );

		$this->assertNull( $clauses->join );
	}

	public function testQuery1ParsesSuccessfully(): void
	{
		$queryBuilder = ( new SqlToQueryBuilderFactory( self::loadFixture( 'query-1.sql' ) ) )->buildQueryBuilder();
		$output       = $queryBuilder->buildAll( ( new SelectBuilder() )->useTable( 't1' ) );

		$this->assertStringContainsString( 'GROUP BY', $output );
		$this->assertStringContainsString( 't1.id', $output );
		$this->assertStringContainsString( 'ORDER BY', $output );
		$this->assertStringContainsString( 't1.createdOn', $output );
		$this->assertStringContainsString( 't1.logisticianId DESC', $output );
	}

	public function testQuery2ExtractsOuterWhere(): void
	{
		$clauses = SqlClauseExtractor::extract( self::loadFixture( 'query-2.sql' ) );

		$this->assertStringContainsString( ':deliveryOrderId', $clauses->where );
		$this->assertStringContainsString( 'do.id', $clauses->where );
	}

	public function testQuery2ExtractsOuterGroupBy(): void
	{
		$clauses = SqlClauseExtractor::extract( self::loadFixture( 'query-2.sql' ) );

		$this->assertSame( 'dli.lineItemId', $clauses->groupBy );
	}

	public function testQuery2ParsesSuccessfully(): void
	{
		$queryBuilder = ( new SqlToQueryBuilderFactory( self::loadFixture( 'query-2.sql' ) ) )->buildQueryBuilder();
		$output       = $queryBuilder->buildAll( ( new SelectBuilder() )->useTable( 'do' ) );

		$this->assertStringContainsString( 'JOIN', $output );
		$this->assertStringContainsString( 'WHERE', $output );
		$this->assertStringContainsString( 'GROUP BY', $output );
		$this->assertStringContainsString( ':deliveryOrderId', $output );
	}

	public function testQuery2SubqueryJoinsAreEmittedWithParens(): void
	{
		$queryBuilder = ( new SqlToQueryBuilderFactory( self::loadFixture( 'query-2.sql' ) ) )->buildQueryBuilder();
		$output       = $queryBuilder->buildAll( ( new SelectBuilder() )->useTable( 'do' ) );

		$this->assertStringContainsString( '(SELECT', $output );
	}
}
