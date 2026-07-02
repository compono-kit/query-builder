<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Tests\Factories;

use ComponoKit\Databases\Sql\QueryBuilder\Exceptions\SqlParseException;
use ComponoKit\Databases\Sql\QueryBuilder\Factories\SqlToQueryBuilderFactory;
use PHPUnit\Framework\TestCase;

class SqlToQueryBuilderFactoryTest extends TestCase
{
	private static function build( string $sql ): string
	{
		$factory = new SqlToQueryBuilderFactory( $sql );

		return $factory->getQueryBuilder()->buildAll( $factory->buildSelectBuilder() );
	}

	public function testSimpleWhereClause(): void
	{
		$output = self::build( "SELECT * FROM users WHERE id = 1" );

		$this->assertStringContainsString( 'WHERE', $output );
		$this->assertStringContainsString( 'id = 1', $output );
	}

	public function testOrderBy(): void
	{
		$output = self::build( "SELECT * FROM users ORDER BY created_at DESC" );

		$this->assertStringContainsString( 'ORDER BY', $output );
		$this->assertStringContainsString( 'DESC', $output );
	}

	public function testLimit(): void
	{
		$output = self::build( "SELECT * FROM users LIMIT 10" );

		$this->assertStringContainsString( 'LIMIT 10', $output );
	}

	public function testLimitWithOffset(): void
	{
		$output = self::build( "SELECT * FROM users LIMIT 10 OFFSET 5" );

		$this->assertStringContainsString( 'LIMIT', $output );
		$this->assertStringContainsString( '10', $output );
	}

	public function testGroupBy(): void
	{
		$output = self::build( "SELECT * FROM users GROUP BY department" );

		$this->assertStringContainsString( 'GROUP BY', $output );
		$this->assertMatchesRegularExpression( '/GROUP BY\s+department/', $output );
	}

	public function testFullQuery(): void
	{
		$sql    = "SELECT * FROM users WHERE id > 0 AND status = 'active' ORDER BY name ASC LIMIT 20 OFFSET 10";
		$output = self::build( $sql );

		$this->assertStringContainsString( 'WHERE', $output );
		$this->assertStringContainsString( 'ORDER BY', $output );
		$this->assertStringContainsString( 'LIMIT', $output );
	}

	public function testQueryWithoutAnyClause(): void
	{
		$queryBuilder = ( new SqlToQueryBuilderFactory( "SELECT * FROM users" ) )->getQueryBuilder();

		$this->assertSame( ' WHERE 1', $queryBuilder->buildWhereStatement() );
	}

	public function testPreparedParameters(): void
	{
		$params = ( new SqlToQueryBuilderFactory( "SELECT * FROM users WHERE id = :id AND name = :name" ) )
			->getQueryBuilder()
			->getPreparedParams();

		$this->assertArrayHasKey( 'id', $params );
		$this->assertArrayHasKey( 'name', $params );
	}

	public function testHavingClause(): void
	{
		$output = self::build( "SELECT * FROM users GROUP BY department HAVING count > 5" );

		$this->assertStringContainsString( 'HAVING', $output );
		$this->assertStringContainsString( 'count > 5', $output );
	}

	public function testHavingComesAfterGroupBy(): void
	{
		$output = self::build( "SELECT * FROM users GROUP BY dept HAVING total > 10 ORDER BY dept" );

		$groupByPosition = strpos( $output, 'GROUP BY' );
		$havingPosition  = strpos( $output, 'HAVING' );
		$orderByPosition = strpos( $output, 'ORDER BY' );

		$this->assertLessThan( $havingPosition, $groupByPosition );
		$this->assertLessThan( $orderByPosition, $havingPosition );
	}

	public function testBuildWhereStatementAfterParsing(): void
	{
		$queryBuilder = ( new SqlToQueryBuilderFactory( "SELECT * FROM users WHERE id = 1" ) )->getQueryBuilder();

		$this->assertStringContainsString( 'id = 1', $queryBuilder->buildWhereStatement() );
	}

	public function testBuildsCompleteStatementFromParsedHead(): void
	{
		$output = self::build( "SELECT * FROM users WHERE id = 1" );

		$this->assertStringContainsString( 'SELECT *', $output );
		$this->assertStringContainsString( 'FROM', $output );
		$this->assertStringContainsString( 'WHERE', $output );
		$this->assertStringContainsString( 'id = 1', $output );
	}

	public function testPreservesSelectExpressionsAndAlias(): void
	{
		$output = self::build( "SELECT id, name FROM users u ORDER BY name ASC" );

		$this->assertStringContainsString( 'SELECT id, name', $output );
		$this->assertMatchesRegularExpression( '/FROM\s+`?users`?\s+u/', $output );
		$this->assertStringContainsString( 'ORDER BY', $output );
	}

	public function testKeepsFunctionExpressionsIntact(): void
	{
		$output = self::build( "SELECT COUNT(id), COALESCE(name, 'n/a') FROM users" );

		$this->assertStringContainsString( "SELECT COUNT(id), COALESCE(name, 'n/a')", $output );
	}

	public function testSelectBuilderThrowsWithoutFromClause(): void
	{
		$this->expectException( SqlParseException::class );

		( new SqlToQueryBuilderFactory( "WHERE id = 1" ) )->buildSelectBuilder();
	}
}
