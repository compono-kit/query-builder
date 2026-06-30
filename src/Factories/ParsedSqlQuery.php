<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Factories;

use ComponoKit\Databases\Sql\QueryBuilder\Builders\QueryBuilder;

class ParsedSqlQuery
{
	public function __construct( private readonly QueryBuilder $queryBuilder )
	{
	}

	public function getQueryBuilder(): QueryBuilder
	{
		return $this->queryBuilder;
	}

	public function buildAll(): string
	{
		return $this->queryBuilder->buildAll();
	}
}
