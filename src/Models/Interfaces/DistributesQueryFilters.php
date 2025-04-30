<?php declare(strict_types=1);

namespace ComponoKit\QueryBuilder\Models\Interfaces;

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

	public function addOrderBy( RepresentsOrderBy $orderBy ): DistributesQueryFilters;

	/**
	 * @param RepresentsOrderBy[] $orderByList
	 *
	 * @return DistributesQueryFilters
	 */
	public function addOrderByList( array $orderByList ): DistributesQueryFilters;

	public function addCriteria( RepresentsCriteria $criteria ): DistributesQueryFilters;

	/**
	 * @param RepresentsCriteria[] $criterias
	 *
	 * @return DistributesQueryFilters
	 */
	public function addCriterias( array $criterias ): DistributesQueryFilters;

	public function getPreparedParams(): array;
}
