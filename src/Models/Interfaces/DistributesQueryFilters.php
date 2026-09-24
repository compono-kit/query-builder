<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Models\Interfaces;

interface DistributesQueryFilters
{
	/**
	 * @return RepresentsCriteria[]
	 */
	public function getCriterias(): array;

	/**
	 * @return RepresentsOrderBy[]
	 */
	public function getOrderByList(): array;

	public function getLimit(): ?RepresentsLimit;

	public function useLimit( RepresentsLimit $limit ): DistributesQueryFilters;

	public function addOrderBy( RepresentsOrderBy ...$orderByList ): DistributesQueryFilters;

	public function addCriteria( RepresentsCriteria ...$criterias ): DistributesQueryFilters;

	public function addHavingCriteria( RepresentsCriteria ...$criterias ): DistributesQueryFilters;

	/**
	 * @return RepresentsCriteria[]
	 */
	public function getHavingCriterias(): array;

	public function addGroupByColumn( RepresentsColumn ...$columns ): DistributesQueryFilters;

	/**
	 * @return RepresentsColumn[]
	 */
	public function getGroupByColumns(): array;

	public function getPreparedParams(): array;

	public function addJoinClause( RepresentsJoinClause ...$joinClauses ): DistributesQueryFilters;

	/**
	 * @return RepresentsJoinClause[]
	 */
	public function getJoinClauses(): array;
}
