<?php declare(strict_types=1);

namespace ComponoKit\QueryBuilder\Models;

use ComponoKit\QueryBuilder\Models\Interfaces\RepresentsColumn;
use ComponoKit\QueryBuilder\Models\Interfaces\RepresentsComparisonValue;
use ComponoKit\QueryBuilder\Models\Interfaces\RepresentsConditionValue;
use ComponoKit\QueryBuilder\Models\Interfaces\RepresentsPreparedParameter;

class ComparisonValueCondition implements RepresentsConditionValue
{
	public function __construct( private readonly RepresentsColumn $column, private readonly RepresentsComparisonValue $value )
	{
	}

	public function getColumn(): RepresentsColumn
	{
		return $this->column;
	}

	public function getPreparedParameter(): ?RepresentsPreparedParameter
	{
		return null;
	}

	public function getValue(): ?RepresentsComparisonValue
	{
		return $this->value;
	}
}
