<?php declare(strict_types=1);

namespace ComponoKit\QueryBuilder\Builders;

use ComponoKit\QueryBuilder\Builders\Interfaces\BuildsQueries;
use ComponoKit\QueryBuilder\Helpers\QueryFilterDistributor;
use ComponoKit\QueryBuilder\Models\Interfaces\DistributesQueryFilters;
use ComponoKit\QueryBuilder\Models\Interfaces\RepresentsColumn;
use ComponoKit\QueryBuilder\Models\Interfaces\RepresentsCriteria;
use ComponoKit\QueryBuilder\Models\Interfaces\RepresentsLimit;
use ComponoKit\QueryBuilder\Models\Interfaces\RepresentsOrderBy;

class QueryBuilder implements BuildsQueries
{
	private readonly DistributesQueryFilters $queryFilterDistributor;

	public function __construct( ?DistributesQueryFilters $queryFilterDistributor = null )
	{
		$this->queryFilterDistributor = $queryFilterDistributor ?? QueryFilterDistributor::newEmpty();
	}

	public function addOrderBy( RepresentsOrderBy $orderBy ): self
	{
		$queryFilterDistributor = $this->queryFilterDistributor->addOrderBy( $orderBy );

		return new self( $queryFilterDistributor );
	}

	/**
	 * @param RepresentsOrderBy[] $orderByList
	 *
	 * @return self
	 */
	public function addOrderByList( array $orderByList ): self
	{
		$queryFilterDistributor = $this->queryFilterDistributor->addOrderByList( $orderByList );

		return new self( $queryFilterDistributor );
	}

	public function useLimit( RepresentsLimit $limit ): self
	{
		$queryFilterDistributor = $this->queryFilterDistributor->useLimit( $limit );

		return new self( $queryFilterDistributor );
	}

	public function addCriteria( RepresentsCriteria $criteria ): self
	{
		$queryFilterDistributor = $this->queryFilterDistributor->addCriteria( $criteria );

		return new self( $queryFilterDistributor );
	}

	/**
	 * @param RepresentsCriteria[] $criterias
	 *
	 * @return $this
	 */
	public function addCriterias( array $criterias ): self
	{
		$queryFilterDistributor = $this->queryFilterDistributor->addCriterias( $criterias );

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

	/**
	 * @param RepresentsColumn[] $groupByColumns
	 *
	 * @return string
	 */
	public function buildGroupBy( array $groupByColumns ): string
	{
		return GroupByBuilder::build( $groupByColumns );
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

	/**
	 * @param RepresentsColumn[] $groupByColumns
	 *
	 * @return string
	 */
	public function buildAll( array $groupByColumns = [] ): string
	{
		return $this->buildWhereStatement() . $this->buildGroupBy( $groupByColumns ) . $this->buildOrderBy() . $this->buildLimit();
	}
}
