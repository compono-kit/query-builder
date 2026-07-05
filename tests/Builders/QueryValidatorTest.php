<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Tests\Builders;

use ComponoKit\Databases\Sql\QueryBuilder\Builders\QueryBuilder;
use ComponoKit\Databases\Sql\QueryBuilder\Builders\QueryValidator;
use ComponoKit\Databases\Sql\QueryBuilder\Builders\SelectBuilder;
use ComponoKit\Databases\Sql\QueryBuilder\Exceptions\ValidationException;
use ComponoKit\Databases\Sql\QueryBuilder\Models\JoinClause;
use ComponoKit\Databases\Sql\QueryBuilder\Models\SubqueryJoinSource;
use ComponoKit\Databases\Sql\QueryBuilder\Models\TableName;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Types\JoinType;
use ComponoKit\Databases\Sql\QueryBuilder\Schema\SchemaParser;
use ComponoKit\Databases\Sql\QueryBuilder\Schema\SchemaRegistry;
use PHPUnit\Framework\TestCase;

class QueryValidatorTest extends TestCase
{
	private static function schema(): SchemaRegistry
	{
		return SchemaParser::fromFile( __DIR__ . '/../fixtures/schema.sql' );
	}

	public function testValidSchemaQueryPasses(): void
	{
		$selectBuilder = ( new SelectBuilder() )
			->from( 'users', 'u' )
			->select( ['u.id', 'u.name', 'u.email'] );

		QueryValidator::validate( $selectBuilder, new QueryBuilder(), self::schema() );

		$this->assertStringContainsString( 'SELECT u.id, u.name, u.email', $selectBuilder->build() );
	}

	public function testInvalidFromTableThrows(): void
	{
		$this->expectException( ValidationException::class );

		QueryValidator::validate(
			( new SelectBuilder() )->from( 'nonexistent_table' ),
			new QueryBuilder(),
			self::schema()
		);
	}

	public function testInvalidJoinTableThrows(): void
	{
		$this->expectException( ValidationException::class );

		QueryValidator::validate(
			( new SelectBuilder() )->from( 'users' ),
			( new QueryBuilder() )->addJoinClause( new JoinClause( new TableName( 'nonexistent_table' ), JoinType::INNER ) ),
			self::schema()
		);
	}

	public function testInvalidColumnInSelectThrows(): void
	{
		$this->expectException( ValidationException::class );

		QueryValidator::validate(
			( new SelectBuilder() )->from( 'users', 'u' )->select( ['u.nonexistent_column'] ),
			new QueryBuilder(),
			self::schema()
		);
	}

	public function testRawExpressionsInSelectAreNotValidated(): void
	{
		QueryValidator::validate(
			( new SelectBuilder() )->from( 'users', 'u' )->select( ['COUNT(*)', '*', 'SUM(u.id)'] ),
			new QueryBuilder(),
			self::schema()
		);

		$this->expectNotToPerformAssertions();
	}

	public function testWildcardSelectIsNotValidated(): void
	{
		QueryValidator::validate(
			( new SelectBuilder() )->from( 'users', 'u' )->select( ['*'] ),
			new QueryBuilder(),
			self::schema()
		);

		$this->expectNotToPerformAssertions();
	}

	public function testAliasResolutionWorksForSelectValidation(): void
	{
		QueryValidator::validate(
			( new SelectBuilder() )->from( 'users', 'u' )->select( ['u.id', 'u.name', 'o.total'] ),
			( new QueryBuilder() )->addJoinClause( new JoinClause( new TableName( 'orders', 'o' ), JoinType::INNER ) ),
			self::schema()
		);

		$this->expectNotToPerformAssertions();
	}

	public function testColumnInWrongTableThrows(): void
	{
		$this->expectException( ValidationException::class );

		QueryValidator::validate(
			( new SelectBuilder() )->from( 'users', 'u' )->select( ['u.total'] ),
			new QueryBuilder(),
			self::schema()
		);
	}

	public function testSubqueryJoinIsSkippedDuringValidation(): void
	{
		$subquery = new SubqueryJoinSource(
			'SELECT id FROM orders WHERE status = \'active\'',
			'sub'
		);

		QueryValidator::validate(
			( new SelectBuilder() )->from( 'users', 'u' )->select( ['u.id'] ),
			( new QueryBuilder() )->addJoinClause( new JoinClause( $subquery, JoinType::LEFT ) ),
			self::schema()
		);

		$this->expectNotToPerformAssertions();
	}
}
