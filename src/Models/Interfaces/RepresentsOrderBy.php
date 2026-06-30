<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Models\Interfaces;

use ComponoKit\Databases\Sql\QueryBuilder\Models\Types\OrderDirection;

interface RepresentsOrderBy
{
	public function getColumn(): RepresentsColumn;

	public function getDirection(): OrderDirection;
}
