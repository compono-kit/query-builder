<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Helpers;

use ComponoKit\Databases\Sql\QueryBuilder\Builders\WhereStatementBuilder;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Interfaces\DistributesQueryFilters;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Interfaces\RepresentsColumn;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Interfaces\RepresentsCriteria;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Interfaces\RepresentsJoinClause;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Interfaces\RepresentsLimit;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Interfaces\RepresentsOrderBy;

class QueryFilterDistributor implements DistributesQueryFilters
{
	/**
	 * @param RepresentsCriteria[]  $whereCriterias
	 * @param RepresentsOrderBy[]   $orderByList
	 * @param RepresentsLimit|null  $limit
	 * @param RepresentsCriteria[]  $havingCriterias
	 * @param RepresentsColumn[]    $groupByColumns
	 * @param RepresentsJoinClause[] $joinClauses
	 */
	public function __construct(
		private readonly array $whereCriterias,
		private readonly array $orderByList = [],
		private readonly ?RepresentsLimit $limit = null,
		private readonly array $havingCriterias = [],
		private readonly array $groupByColumns = [],
		private readonly array $joinClauses = []
	) {
	}

	public static function newEmpty(): self
	{
		return new self( [] );
	}

	public function getCriterias(): array
	{
		return $this->whereCriterias;
	}

	public function getOrderByList(): array
	{
		return $this->orderByList;
	}

	public function getLimit(): ?RepresentsLimit
	{
		return $this->limit;
	}

	public function useLimit( RepresentsLimit $limit ): DistributesQueryFilters
	{
		return new self( $this->whereCriterias, $this->orderByList, $limit, $this->havingCriterias, $this->groupByColumns, $this->joinClauses );
	}

	public function addOrderBy( RepresentsOrderBy $orderBy ): self
	{
		$orderByList   = $this->orderByList;
		$orderByList[] = $orderBy;

		return new self( $this->whereCriterias, $orderByList, $this->limit, $this->havingCriterias, $this->groupByColumns, $this->joinClauses );
	}

	/**
	 * @param RepresentsOrderBy[] $orderByList
	 *
	 * @return self
	 */
	public function addOrderByList( array $orderByList ): self
	{
		$updatedOrderByList = $this->orderByList;

		foreach ( $orderByList as $orderBy )
		{
			$updatedOrderByList[] = $orderBy;
		}

		return new self( $this->whereCriterias, $updatedOrderByList, $this->limit, $this->havingCriterias, $this->groupByColumns, $this->joinClauses );
	}

	public function addCriteria( RepresentsCriteria $criteria ): self
	{
		$criterias   = $this->whereCriterias;
		$criterias[] = $criteria;

		return new self( $criterias, $this->orderByList, $this->limit, $this->havingCriterias, $this->groupByColumns, $this->joinClauses );
	}

	/**
	 * @param RepresentsCriteria[] $criterias
	 *
	 * @return $this
	 */
	public function addCriterias( array $criterias ): self
	{
		$allCriterias = $this->whereCriterias;

		foreach ( $criterias as $additionalCriteria )
		{
			$allCriterias[] = $additionalCriteria;
		}

		return new self( $allCriterias, $this->orderByList, $this->limit, $this->havingCriterias, $this->groupByColumns, $this->joinClauses );
	}

	public function addHavingCriteria( RepresentsCriteria $criteria ): self
	{
		$havingCriterias   = $this->havingCriterias;
		$havingCriterias[] = $criteria;

		return new self( $this->whereCriterias, $this->orderByList, $this->limit, $havingCriterias, $this->groupByColumns, $this->joinClauses );
	}

	public function getHavingCriterias(): array
	{
		return $this->havingCriterias;
	}

	public function addGroupByColumn( RepresentsColumn $column ): self
	{
		$groupByColumns   = $this->groupByColumns;
		$groupByColumns[] = $column;

		return new self( $this->whereCriterias, $this->orderByList, $this->limit, $this->havingCriterias, $groupByColumns, $this->joinClauses );
	}

	/**
	 * @param RepresentsColumn[] $groupByColumns
	 *
	 * @return self
	 */
	public function addGroupByList( array $groupByColumns ): self
	{
		$updatedGroupByColumns = $this->groupByColumns;

		foreach ( $groupByColumns as $column )
		{
			$updatedGroupByColumns[] = $column;
		}

		return new self( $this->whereCriterias, $this->orderByList, $this->limit, $this->havingCriterias, $updatedGroupByColumns, $this->joinClauses );
	}

	public function getGroupByColumns(): array
	{
		return $this->groupByColumns;
	}

	public function getPreparedParams(): array
	{
		return WhereStatementBuilder::getPreparedParams( $this->getCriterias() );
	}

	public function addJoinClause( RepresentsJoinClause $joinClause ): self
	{
		$joinClauses   = $this->joinClauses;
		$joinClauses[] = $joinClause;

		return new self( $this->whereCriterias, $this->orderByList, $this->limit, $this->havingCriterias, $this->groupByColumns, $joinClauses );
	}

	/**
	 * @param RepresentsJoinClause[] $joinClauses
	 */
	public function addJoinClauses( array $joinClauses ): self
	{
		$updatedJoinClauses = $this->joinClauses;

		foreach ( $joinClauses as $joinClause )
		{
			$updatedJoinClauses[] = $joinClause;
		}

		return new self( $this->whereCriterias, $this->orderByList, $this->limit, $this->havingCriterias, $this->groupByColumns, $updatedJoinClauses );
	}

	public function getJoinClauses(): array
	{
		return $this->joinClauses;
	}
}
