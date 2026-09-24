<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Builders\Interfaces;

interface BuildsStatements
{
	public function build( BuildsQueries $queryBuilder ): string;

	public function getPreparedParams( BuildsQueries $queryBuilder ): array;
}
