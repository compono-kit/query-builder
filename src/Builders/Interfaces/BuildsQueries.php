<?php declare(strict_types=1);

namespace ComponoKit\QueryBuilder\Builders\Interfaces;

use ComponoKit\QueryBuilder\Models\Interfaces\RepresentsColumn;

interface BuildsQueries
{
	public function buildWhereStatement(): string;

	public function getPreparedParams(): array;

	/**
	 * @param RepresentsColumn[] $groupByColumns
	 *
	 * @return string
	 */
	public function buildGroupBy( array $groupByColumns ): string;

	public function buildOrderBy(): string;

	public function buildLimit(): string;

	/**
	 * @param RepresentsColumn[] $groupByColumns
	 *
	 * @return string
	 */
	public function buildAll( array $groupByColumns = [] ): string;
}
