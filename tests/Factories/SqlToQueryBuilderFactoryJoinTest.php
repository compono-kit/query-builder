<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Tests\Factories;

use ComponoKit\Databases\Sql\QueryBuilder\Factories\SqlToQueryBuilderFactory;
use PHPUnit\Framework\TestCase;

class SqlToQueryBuilderFactoryJoinTest extends TestCase
{
	private static function build( string $sql ): string
	{
		$factory = new SqlToQueryBuilderFactory( $sql );

		return $factory->buildQueryBuilder()->buildAll( $factory->buildSelectBuilder() );
	}

	public function testSingleInnerJoin(): void
	{
		$output = self::build( 'SELECT * FROM users u INNER JOIN orders o ON u.id = o.user_id' );

		$this->assertStringContainsString( 'INNER JOIN', $output );
		$this->assertStringContainsString( 'orders', $output );
	}

	public function testJoinComesBeforeWhere(): void
	{
		$output = self::build( 'SELECT * FROM users u INNER JOIN orders o ON u.id = o.user_id WHERE u.active = 1' );

		$this->assertLessThan( strpos( $output, 'WHERE' ), strpos( $output, 'JOIN' ) );
	}

	public function testMultipleJoins(): void
	{
		$sql    = 'SELECT * FROM users u INNER JOIN orders o ON u.id = o.user_id LEFT JOIN products p ON o.product_id = p.id';
		$output = self::build( $sql );

		$this->assertStringContainsString( 'INNER JOIN', $output );
		$this->assertStringContainsString( 'LEFT JOIN', $output );
	}

	public function testJoinWithoutWhereClause(): void
	{
		$output = self::build( 'SELECT * FROM users u INNER JOIN orders o ON u.id = o.user_id' );

		$this->assertStringContainsString( 'JOIN', $output );
		$this->assertStringContainsString( 'WHERE 1', $output );
	}

	public function testJoinCombinedWithGroupByAndOrderBy(): void
	{
		$sql    = 'SELECT * FROM users u INNER JOIN orders o ON u.id = o.user_id GROUP BY u.id ORDER BY u.name ASC';
		$output = self::build( $sql );

		$joinPosition    = strpos( $output, 'JOIN' );
		$groupByPosition = strpos( $output, 'GROUP BY' );
		$orderByPosition = strpos( $output, 'ORDER BY' );

		$this->assertLessThan( $groupByPosition, $joinPosition );
		$this->assertLessThan( $orderByPosition, $groupByPosition );
	}
}
