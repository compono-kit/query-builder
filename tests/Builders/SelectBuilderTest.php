<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Tests\Builders;

use ComponoKit\Databases\Sql\QueryBuilder\Builders\QueryBuilder;
use ComponoKit\Databases\Sql\QueryBuilder\Builders\SelectBuilder;
use PHPUnit\Framework\TestCase;

class SelectBuilderTest extends TestCase
{
	public function testBuildProducesCorrectSql(): void
	{
		$output = ( new QueryBuilder() )->buildAll(
			( new SelectBuilder() )->useTable( 'users' )
		);

		$this->assertStringContainsString( 'SELECT *', $output );
		$this->assertStringContainsString( 'FROM users', $output );
	}

	public function testSelectExpressionsAreRendered(): void
	{
		$output = ( new SelectBuilder() )
			->useTable( 'users', 'u' )
			->useSelectExpressions( 'u.id', 'u.name', 'COUNT(*)' )
			->buildSelectFrom();

		$this->assertStringContainsString( 'SELECT u.id, u.name, COUNT(*)', $output );
		$this->assertStringContainsString( 'FROM users u', $output );
	}

	public function testEmptySelectDefaultsToWildcard(): void
	{
		$output = ( new SelectBuilder() )
			->useTable( 'users' )
			->useSelectExpressions()
			->buildSelectFrom();

		$this->assertStringContainsString( 'SELECT *', $output );
	}

	public function testBuildThrowsWhenFromNotSet(): void
	{
		$this->expectException( \LogicException::class );

		( new SelectBuilder() )->buildSelectFrom();
	}

	public function testFromSqlParsesSelectAndFrom(): void
	{
		$output = SelectBuilder::fromSql( 'id, name', 'users u' )->buildSelectFrom();

		$this->assertSame( 'SELECT id, name FROM users u', $output );
	}

	public function testHeadComesBeforeClauseTail(): void
	{
		$output = ( new QueryBuilder() )->buildAll(
			( new SelectBuilder() )->useTable( 'users' )
		);

		$selectPosition = strpos( $output, 'SELECT' );
		$fromPosition   = strpos( $output, 'FROM' );

		$this->assertLessThan( $fromPosition, $selectPosition );
	}
}
