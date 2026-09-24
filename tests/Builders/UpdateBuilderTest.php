<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Tests\Builders;

use ComponoKit\Databases\Sql\QueryBuilder\Builders\QueryBuilder;
use ComponoKit\Databases\Sql\QueryBuilder\Builders\UpdateBuilder;
use ComponoKit\Databases\Sql\QueryBuilder\Criterias\Conditions\Condition;
use ComponoKit\Databases\Sql\QueryBuilder\Exceptions\MissingValueException;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Column;
use ComponoKit\Databases\Sql\QueryBuilder\Models\ColumnName;
use ComponoKit\Databases\Sql\QueryBuilder\Models\ComparisonColumn;
use ComponoKit\Databases\Sql\QueryBuilder\Models\ComparisonValue;
use ComponoKit\Databases\Sql\QueryBuilder\Models\ComparisonValueCondition;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Interfaces\RepresentsColumn;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Interfaces\RepresentsComparisonValue;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Interfaces\RepresentsConditionValue;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Interfaces\RepresentsPreparedParameter;
use ComponoKit\Databases\Sql\QueryBuilder\Models\JoinClause;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Limit;
use ComponoKit\Databases\Sql\QueryBuilder\Models\OrderBy;
use ComponoKit\Databases\Sql\QueryBuilder\Models\PreparedParameter;
use ComponoKit\Databases\Sql\QueryBuilder\Models\PreparedParameterCondition;
use ComponoKit\Databases\Sql\QueryBuilder\Models\TableName;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Types\ComparisonOperator;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Types\JoinType;
use PHPUnit\Framework\TestCase;

class UpdateBuilderTest extends TestCase
{
	public function testBuildRendersPreparedParameterAndLiterals(): void
	{
		$updateBuilder = ( new UpdateBuilder() )
			->useTable( 'users' )
			->addConditionValue( new PreparedParameterCondition( $this->column( 'name' ), new PreparedParameter( 'name', 'Max' ) ) )
			->addConditionValue(
				new ComparisonValueCondition( $this->column( 'status' ), new ComparisonValue( 'active' ) ),
				new ComparisonValueCondition( $this->column( 'age' ), new ComparisonValue( 42 ) )
			);

		$output = ( new QueryBuilder() )->buildAll( $updateBuilder );

		$this->assertSame( "UPDATE users SET name = :name, status = 'active', age = 42 WHERE 1", $output );
	}

	public function testTableAliasIsRendered(): void
	{
		$updateBuilder = ( new UpdateBuilder() )
			->useTable( 'users', 'u' )
			->addConditionValue( new ComparisonValueCondition( $this->column( 'age' ), new ComparisonValue( 1 ) ) );

		$this->assertSame( 'UPDATE users u SET age = 1 WHERE 1', ( new QueryBuilder() )->buildAll( $updateBuilder ) );
	}

	public function testMissingTableThrowsException(): void
	{
		$this->expectException( \LogicException::class );

		( new QueryBuilder() )->buildAll(
			( new UpdateBuilder() )->addConditionValue( new ComparisonValueCondition( $this->column( 'age' ), new ComparisonValue( 1 ) ) )
		);
	}

	public function testMissingConditionValuesThrowsException(): void
	{
		$this->expectException( \LogicException::class );

		( new QueryBuilder() )->buildAll( ( new UpdateBuilder() )->useTable( 'users' ) );
	}

	public function testConditionValueWithoutValueThrowsException(): void
	{
		$conditionValue = new class( $this->column( 'age' ) ) implements RepresentsConditionValue {
			public function __construct( private readonly RepresentsColumn $column )
			{
			}

			public function getColumn(): RepresentsColumn
			{
				return $this->column;
			}

			public function getPreparedParameter(): ?RepresentsPreparedParameter
			{
				return null;
			}

			public function getValue(): ?RepresentsComparisonValue
			{
				return null;
			}
		};

		$this->expectException( MissingValueException::class );

		( new QueryBuilder() )->buildAll( ( new UpdateBuilder() )->useTable( 'users' )->addConditionValue( $conditionValue ) );
	}

	public function testGroupByThrowsException(): void
	{
		$this->expectException( \LogicException::class );

		( new QueryBuilder() )
			->addGroupByColumn( $this->column( 'status' ) )
			->buildAll(
				( new UpdateBuilder() )
					->useTable( 'users' )
					->addConditionValue( new ComparisonValueCondition( $this->column( 'age' ), new ComparisonValue( 1 ) ) )
			);
	}

	public function testBuildRendersCompleteUpdateStatement(): void
	{
		$joinCondition = new Condition(
			new Column( new TableName( 'u' ), new ColumnName( 'id' ) ),
			ComparisonOperator::equalOperator(),
			null,
			new ComparisonColumn( new Column( new TableName( 'o' ), new ColumnName( 'user_id' ) ) )
		);

		$updateBuilder = ( new UpdateBuilder() )
			->useTable( 'users', 'u' )
			->addConditionValue( new PreparedParameterCondition( $this->column( 'name' ), new PreparedParameter( 'name', 'Max' ) ) );

		$output = ( new QueryBuilder() )
			->addJoinClause( new JoinClause( new TableName( 'orders', 'o' ), JoinType::INNER, $joinCondition ) )
			->addCriteria( $this->idCriteria( '5' ) )
			->addOrderBy( new OrderBy( $this->column( 'id' ) ) )
			->useLimit( new Limit( 10 ) )
			->buildAll( $updateBuilder );

		$this->assertStringStartsWith( 'UPDATE users u INNER JOIN', $output );
		$this->assertLessThan( strpos( $output, ' SET ' ), strpos( $output, 'INNER JOIN' ) );
		$this->assertLessThan( strpos( $output, ' WHERE ' ), strpos( $output, ' SET ' ) );
		$this->assertLessThan( strpos( $output, ' ORDER BY ' ), strpos( $output, ' WHERE ' ) );
		$this->assertStringEndsWith( ' LIMIT 10', $output );
	}

	public function testGetPreparedParamsReturnsConditionValueParameters(): void
	{
		$updateBuilder = ( new UpdateBuilder() )
			->useTable( 'users' )
			->addConditionValue(
				new PreparedParameterCondition( $this->column( 'name' ), new PreparedParameter( 'name', 'Max' ) ),
				new ComparisonValueCondition( $this->column( 'age' ), new ComparisonValue( 1 ) )
			);

		$this->assertSame( ['name' => 'Max'], ( new QueryBuilder() )->getStatementPreparedParams( $updateBuilder ) );
	}

	public function testUpdatePreparedParamsAreMerged(): void
	{
		$updateBuilder = ( new UpdateBuilder() )
			->useTable( 'users' )
			->addConditionValue( new PreparedParameterCondition( $this->column( 'name' ), new PreparedParameter( 'name', 'Max' ) ) );

		$params = ( new QueryBuilder() )
			->addCriteria( $this->idCriteria( '5' ) )
			->getStatementPreparedParams( $updateBuilder );

		$this->assertSame( ['name' => 'Max', 'id' => '5'], $params );
	}

	public function testConflictingPreparedParamsThrowException(): void
	{
		$this->expectException( \LogicException::class );

		$updateBuilder = ( new UpdateBuilder() )
			->useTable( 'users' )
			->addConditionValue( new PreparedParameterCondition( $this->column( 'id' ), new PreparedParameter( 'id', '6' ) ) );

		( new QueryBuilder() )
			->addCriteria( $this->idCriteria( '5' ) )
			->getStatementPreparedParams( $updateBuilder );
	}

	private function column( string $name ): Column
	{
		return new Column( new TableName( '' ), new ColumnName( $name ) );
	}

	private function idCriteria( string $value ): Condition
	{
		return new Condition( $this->column( 'id' ), ComparisonOperator::equalOperator(), new PreparedParameter( 'id', $value ), null );
	}
}
