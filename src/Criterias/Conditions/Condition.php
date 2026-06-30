<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Criterias\Conditions;

use ComponoKit\Databases\Sql\QueryBuilder\Models\Interfaces\RepresentsColumn;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Interfaces\RepresentsComparisonValue;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Interfaces\RepresentsPreparedParameter;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Types\ComparisonOperator;

class Condition extends AbstractDefaultCondition
{
	public function __construct( private readonly RepresentsColumn $column, private readonly ComparisonOperator $comparisonOperator, private readonly ?RepresentsPreparedParameter $preparedParameter, private readonly ?RepresentsComparisonValue $value )
	{
	}

	protected function getColumn(): RepresentsColumn
	{
		return $this->column;
	}

	protected function getComparisonOperator(): ComparisonOperator
	{
		return $this->comparisonOperator;
	}

	protected function getPreparedParameter(): ?RepresentsPreparedParameter
	{
		return $this->preparedParameter;
	}

	protected function getValue(): ?RepresentsComparisonValue
	{
		return $this->value;
	}
}
