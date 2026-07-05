<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Builders;

use ComponoKit\Databases\Sql\QueryBuilder\Builders\Interfaces\BuildsQueries;
use ComponoKit\Databases\Sql\QueryBuilder\Helpers\QueryFilterDistributor;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Interfaces\DistributesQueryFilters;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Interfaces\RepresentsColumn;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Interfaces\RepresentsCriteria;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Interfaces\RepresentsJoinClause;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Interfaces\RepresentsLimit;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Interfaces\RepresentsOrderBy;

class QueryBuilder implements BuildsQueries
{
	private readonly DistributesQueryFilters $queryFilterDistributor;

	public function __construct( ?DistributesQueryFilters $queryFilterDistributor = null )
	{
		$this->queryFilterDistributor = $queryFilterDistributor ?? QueryFilterDistributor::newEmpty();
	}

	public function addOrderBy( RepresentsOrderBy $orderBy ): static
	{
		$queryFilterDistributor = $this->queryFilterDistributor->addOrderBy( $orderBy );

		return new self( $queryFilterDistributor );
	}

	/**
	 * @param RepresentsOrderBy[] $orderByList
	 */
	public function addOrderByList( array $orderByList ): static
	{
		$queryFilterDistributor = $this->queryFilterDistributor->addOrderByList( $orderByList );

		return new self( $queryFilterDistributor );
	}

	public function useLimit( RepresentsLimit $limit ): static
	{
		$queryFilterDistributor = $this->queryFilterDistributor->useLimit( $limit );

		return new self( $queryFilterDistributor );
	}

	public function addCriteria( RepresentsCriteria $criteria ): static
	{
		$queryFilterDistributor = $this->queryFilterDistributor->addCriteria( $criteria );

		return new self( $queryFilterDistributor );
	}

	/**
	 * @param RepresentsCriteria[] $criterias
	 */
	public function addCriterias( array $criterias ): static
	{
		$queryFilterDistributor = $this->queryFilterDistributor->addCriterias( $criterias );

		return new self( $queryFilterDistributor );
	}

	public function addHavingCriteria( RepresentsCriteria $criteria ): static
	{
		$queryFilterDistributor = $this->queryFilterDistributor->addHavingCriteria( $criteria );

		return new self( $queryFilterDistributor );
	}

	public function addGroupByColumn( RepresentsColumn $column ): static
	{
		$queryFilterDistributor = $this->queryFilterDistributor->addGroupByColumn( $column );

		return new self( $queryFilterDistributor );
	}

	/**
	 * @param RepresentsColumn[] $groupByColumns
	 */
	public function addGroupByList( array $groupByColumns ): static
	{
		$queryFilterDistributor = $this->queryFilterDistributor->addGroupByList( $groupByColumns );

		return new self( $queryFilterDistributor );
	}

	public function addJoinClause( RepresentsJoinClause $joinClause ): static
	{
		$queryFilterDistributor = $this->queryFilterDistributor->addJoinClause( $joinClause );

		return new self( $queryFilterDistributor );
	}

	/**
	 * @param RepresentsJoinClause[] $joinClauses
	 */
	public function addJoinClauses( array $joinClauses ): static
	{
		$queryFilterDistributor = $this->queryFilterDistributor->addJoinClauses( $joinClauses );

		return new self( $queryFilterDistributor );
	}

	public function getJoinClauses(): array
	{
		return $this->queryFilterDistributor->getJoinClauses();
	}

	public function buildJoin(): string
	{
		return ( new JoinBuilder( $this->queryFilterDistributor->getJoinClauses() ) )->build();
	}

	public function buildWhereStatement(): string
	{
		return ( new WhereStatementBuilder( $this->queryFilterDistributor->getCriterias() ) )->buildWhereStatement();
	}

	public function getPreparedParams(): array
	{
		return ( new WhereStatementBuilder( $this->queryFilterDistributor->getCriterias() ) )->getPreparedParams();
	}

	public function buildGroupBy(): string
	{
		return ( new GroupByBuilder( $this->queryFilterDistributor->getGroupByColumns() ) )->build();
	}

	public function buildOrderBy(): string
	{
		return ( new OrderByBuilder( $this->queryFilterDistributor->getOrderByList() ) )->build();
	}

	public function buildLimit(): string
	{
		return ( new LimitBuilder( $this->queryFilterDistributor->getLimit() ) )->build();
	}

	public function buildHaving(): string
	{
		return ( new HavingBuilder( $this->queryFilterDistributor->getHavingCriterias() ) )->build();
	}

	public function buildAll( SelectBuilder $selectBuilder ): string
	{
		return $selectBuilder->build() . $this->buildJoin() . $this->buildWhereStatement() . $this->buildGroupBy() . $this->buildHaving() . $this->buildOrderBy() . $this->buildLimit();
	}
}
