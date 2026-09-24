<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Builders;

use ComponoKit\Databases\Sql\QueryBuilder\Exceptions\MissingValueException;
use ComponoKit\Databases\Sql\QueryBuilder\Helpers\ComparisonValueFormatter;
use ComponoKit\Databases\Sql\QueryBuilder\Helpers\QueryFilterDistributor;
use ComponoKit\Databases\Sql\QueryBuilder\Helpers\Quoter;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Interfaces\DistributesQueryFilters;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Interfaces\RepresentsConditionValue;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Interfaces\RepresentsCriteria;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Interfaces\RepresentsJoinClause;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Interfaces\RepresentsLimit;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Interfaces\RepresentsOrderBy;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Interfaces\RepresentsTableName;
use ComponoKit\Databases\Sql\QueryBuilder\Models\TableName;

class UpdateBuilder
{
	private readonly DistributesQueryFilters $queryFilterDistributor;

	/**
	 * @param RepresentsConditionValue[] $conditionValues
	 */
	public function __construct(
		private readonly ?RepresentsTableName $table = null,
		private readonly array $conditionValues = [],
		?DistributesQueryFilters $queryFilterDistributor = null
	) {
		$this->queryFilterDistributor = $queryFilterDistributor ?? QueryFilterDistributor::newEmpty();
	}

	public function useTable( string $table, ?string $alias = null ): self
	{
		return new self( new TableName( $table, $alias ), $this->conditionValues, $this->queryFilterDistributor );
	}

	public function addConditionValue( RepresentsConditionValue ...$conditionValues ): self
	{
		return new self( $this->table, [...$this->conditionValues, ...$conditionValues], $this->queryFilterDistributor );
	}

	public function addCriteria( RepresentsCriteria ...$criterias ): self
	{
		return new self( $this->table, $this->conditionValues, $this->queryFilterDistributor->addCriteria( ...$criterias ) );
	}

	public function addJoinClause( RepresentsJoinClause ...$joinClauses ): self
	{
		return new self( $this->table, $this->conditionValues, $this->queryFilterDistributor->addJoinClause( ...$joinClauses ) );
	}

	public function addOrderBy( RepresentsOrderBy ...$orderByList ): self
	{
		return new self( $this->table, $this->conditionValues, $this->queryFilterDistributor->addOrderBy( ...$orderByList ) );
	}

	public function useLimit( RepresentsLimit $limit ): self
	{
		return new self( $this->table, $this->conditionValues, $this->queryFilterDistributor->useLimit( $limit ) );
	}

	public function getTable(): ?RepresentsTableName
	{
		return $this->table;
	}

	/**
	 * @return RepresentsConditionValue[]
	 */
	public function getConditionValues(): array
	{
		return $this->conditionValues;
	}

	public function build(): string
	{
		return $this->buildTable()
			. ( new JoinBuilder( $this->queryFilterDistributor->getJoinClauses() ) )->build()
			. $this->buildSet()
			. ( new WhereStatementBuilder( $this->queryFilterDistributor->getCriterias() ) )->buildWhereStatement()
			. ( new OrderByBuilder( $this->queryFilterDistributor->getOrderByList() ) )->build()
			. ( new LimitBuilder( $this->queryFilterDistributor->getLimit() ) )->build();
	}

	public function getPreparedParams(): array
	{
		$params = [];
		foreach ( $this->conditionValues as $conditionValue )
		{
			if ( null !== $conditionValue->getPreparedParameter() )
			{
				$params[ $conditionValue->getPreparedParameter()->getName() ] = $conditionValue->getPreparedParameter()->getValue();
			}
		}

		foreach ( $this->queryFilterDistributor->getPreparedParams() as $name => $value )
		{
			if ( array_key_exists( $name, $params ) && $params[ $name ] !== $value )
			{
				throw new \LogicException( sprintf( 'Prepared parameter "%s" is used with different values in SET and WHERE.', $name ) );
			}

			$params[ $name ] = $value;
		}

		return $params;
	}

	private function buildTable(): string
	{
		if ( $this->table === null )
		{
			throw new \LogicException( 'Cannot build query: useTable() has not been called.' );
		}

		$sql = 'UPDATE ' . Quoter::quote( $this->table->getName() );

		if ( $this->table->hasAlias() )
		{
			$sql .= ' ' . $this->table->getAlias();
		}

		return $sql;
	}

	private function buildSet(): string
	{
		if ( !$this->conditionValues )
		{
			throw new \LogicException( 'Cannot build query: addConditionValue() has not been called.' );
		}

		$conditionValueStatements = [];
		foreach ( $this->conditionValues as $conditionValue )
		{
			$conditionValueStatements[] = $this->buildConditionValue( $conditionValue );
		}

		return ' SET ' . implode( ', ', $conditionValueStatements );
	}

	private function buildConditionValue( RepresentsConditionValue $conditionValue ): string
	{
		if ( null !== $conditionValue->getPreparedParameter() )
		{
			return sprintf( '%s = :%s', $conditionValue->getColumn()->toString(), $conditionValue->getPreparedParameter()->getName() );
		}

		if ( null === $conditionValue->getValue() )
		{
			throw new MissingValueException( 'You must either provide a prepared parameter or a value' );
		}

		return sprintf( '%s = %s', $conditionValue->getColumn()->toString(), ComparisonValueFormatter::format( $conditionValue->getValue() ) );
	}
}
