<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Builders\Interfaces;

use ComponoKit\Databases\Sql\QueryBuilder\Models\Interfaces\RepresentsColumn;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Interfaces\RepresentsCriteria;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Interfaces\RepresentsJoinClause;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Interfaces\RepresentsLimit;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Interfaces\RepresentsOrderBy;

interface BuildsQueries
{
	public function buildJoin(): string;

	public function buildWhereStatement(): string;

	public function getPreparedParams(): array;

	public function buildGroupBy(): string;

	public function buildOrderBy(): string;

	public function buildLimit(): string;

	public function buildHaving(): string;

	public function buildAll(): string;

	public function addCriteria( RepresentsCriteria $criteria ): static;

	public function addCriterias( array $criterias ): static;

	public function addOrderBy( RepresentsOrderBy $orderBy ): static;

	/**
	 * @param RepresentsOrderBy[] $orderByList
	 */
	public function addOrderByList( array $orderByList ): static;

	public function addGroupByColumn( RepresentsColumn $column ): static;

	/**
	 * @param RepresentsColumn[] $groupByColumns
	 */
	public function addGroupByList( array $groupByColumns ): static;

	public function addHavingCriteria( RepresentsCriteria $criteria ): static;

	public function useLimit( RepresentsLimit $limit ): static;

	public function addJoinClause( RepresentsJoinClause $joinClause ): static;

	/**
	 * @param RepresentsJoinClause[] $joinClauses
	 */
	public function addJoinClauses( array $joinClauses ): static;
}
