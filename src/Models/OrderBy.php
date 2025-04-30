<?php declare(strict_types=1);

namespace ComponoKit\QueryBuilder\Models;

use ComponoKit\QueryBuilder\Models\Interfaces\RepresentsColumn;
use ComponoKit\QueryBuilder\Models\Interfaces\RepresentsOrderBy;
use ComponoKit\QueryBuilder\Models\Types\OrderDirection;

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
