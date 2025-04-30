<?php declare(strict_types=1);

namespace ComponoKit\QueryBuilder\Models\Interfaces;

use ComponoKit\QueryBuilder\Models\Types\OrderDirection;

interface RepresentsOrderBy
{
	public function getColumn(): RepresentsColumn;

	public function getDirection(): OrderDirection;
}
