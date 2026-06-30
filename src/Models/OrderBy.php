<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Models;

use ComponoKit\Databases\Sql\QueryBuilder\Models\Interfaces\RepresentsColumn;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Interfaces\RepresentsOrderBy;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Types\OrderDirection;

class OrderBy implements RepresentsOrderBy
{
	private OrderDirection       $direction;

	public function __construct( private readonly RepresentsColumn $column, ?OrderDirection $direction = null )
	{
		$this->direction  = $direction ?? OrderDirection::ASC;
	}

	public function getColumn(): RepresentsColumn
	{
		return $this->column;
	}

	public function getDirection(): OrderDirection
	{
		return $this->direction;
	}
}
