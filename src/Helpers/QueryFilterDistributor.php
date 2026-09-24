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

	public function addOrderBy( RepresentsOrderBy ...$orderByList ): self
	{
		return new self( $this->whereCriterias, [...$this->orderByList, ...$orderByList], $this->limit, $this->havingCriterias, $this->groupByColumns, $this->joinClauses );
	}

	public function addCriteria( RepresentsCriteria ...$criterias ): self
	{
		return new self( [...$this->whereCriterias, ...$criterias], $this->orderByList, $this->limit, $this->havingCriterias, $this->groupByColumns, $this->joinClauses );
	}

	public function addHavingCriteria( RepresentsCriteria ...$criterias ): self
	{
		return new self( $this->whereCriterias, $this->orderByList, $this->limit, [...$this->havingCriterias, ...$criterias], $this->groupByColumns, $this->joinClauses );
	}

	public function getHavingCriterias(): array
	{
		return $this->havingCriterias;
	}

	public function addGroupByColumn( RepresentsColumn ...$columns ): self
	{
		return new self( $this->whereCriterias, $this->orderByList, $this->limit, $this->havingCriterias, [...$this->groupByColumns, ...$columns], $this->joinClauses );
	}

	public function getGroupByColumns(): array
	{
		return $this->groupByColumns;
	}

	public function getPreparedParams(): array
	{
		return ( new WhereStatementBuilder( $this->getCriterias() ) )->getPreparedParams();
	}

	public function addJoinClause( RepresentsJoinClause ...$joinClauses ): self
	{
		return new self( $this->whereCriterias, $this->orderByList, $this->limit, $this->havingCriterias, $this->groupByColumns, [...$this->joinClauses, ...$joinClauses] );
	}

	public function getJoinClauses(): array
	{
		return $this->joinClauses;
	}
}
