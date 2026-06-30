<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Builders;

use ComponoKit\Databases\Sql\QueryBuilder\Builders\Interfaces\BuildsQueries;
use ComponoKit\Databases\Sql\QueryBuilder\Helpers\QueryFilterDistributor;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Interfaces\DistributesQueryFilters;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Interfaces\RepresentsColumn;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Interfaces\RepresentsCriteria;
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

	public function buildWhereStatement(): string
	{
		return WhereStatementBuilder::buildWhereStatement( $this->queryFilterDistributor->getCriterias() );
	}

	public function getPreparedParams(): array
	{
		return WhereStatementBuilder::getPreparedParams( $this->queryFilterDistributor->getCriterias() );
	}

	public function buildGroupBy(): string
	{
		return GroupByBuilder::build( $this->queryFilterDistributor->getGroupByColumns() );
	}

	public function buildOrderBy(): string
	{
		return OrderByBuilder::build( $this->queryFilterDistributor->getOrderByList() );
	}

	public function buildLimit(): string
	{
		if ( null !== $this->queryFilterDistributor->getLimit() )
		{
			return LimitBuilder::build( $this->queryFilterDistributor->getLimit() );
		}

		return '';
	}

	public function buildHaving(): string
	{
		return HavingBuilder::build( $this->queryFilterDistributor->getHavingCriterias() );
	}

	public function buildAll(): string
	{
		return $this->buildWhereStatement() . $this->buildGroupBy() . $this->buildHaving() . $this->buildOrderBy() . $this->buildLimit();
	}
}
