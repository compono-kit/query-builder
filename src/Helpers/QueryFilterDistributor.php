<?php declare(strict_types=1);

namespace ComponoKit\QueryBuilder\Helpers;

use ComponoKit\QueryBuilder\Builders\WhereStatementBuilder;
use ComponoKit\QueryBuilder\Models\Interfaces\DistributesQueryFilters;
use ComponoKit\QueryBuilder\Models\Interfaces\RepresentsCriteria;
use ComponoKit\QueryBuilder\Models\Interfaces\RepresentsLimit;
use ComponoKit\QueryBuilder\Models\Interfaces\RepresentsOrderBy;

class QueryFilterDistributor implements DistributesQueryFilters
{
	/**
	 * @param RepresentsCriteria[] $whereCriterias
	 * @param RepresentsOrderBy[]  $orderByList
	 * @param RepresentsLimit|null $limit
	 */
	public function __construct( private readonly array $whereCriterias, private readonly array $orderByList = [], private readonly ?RepresentsLimit $limit = null )
	{
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
		return new self( $this->whereCriterias, $this->orderByList, $limit );
	}

	public function addOrderBy( RepresentsOrderBy $orderBy ): self
	{
		$orderByList   = $this->orderByList;
		$orderByList[] = $orderBy;

		return new self( $this->whereCriterias, $orderByList, $this->limit );
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

		return new self( $this->whereCriterias, $updatedOrderByList, $this->limit );
	}

	public function addCriteria( RepresentsCriteria $criteria ): self
	{
		$criterias   = $this->whereCriterias;
		$criterias[] = $criteria;

		return new self( $criterias, $this->orderByList, $this->limit );
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

		return new self( $allCriterias, $this->orderByList, $this->limit );
	}

	public function getPreparedParams(): array
	{
		return WhereStatementBuilder::getPreparedParams( $this->getCriterias() );
	}
}
