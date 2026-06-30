<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Models;

use ComponoKit\Databases\Sql\QueryBuilder\Models\Interfaces\RepresentsColumn;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Interfaces\RepresentsComparisonValue;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Interfaces\RepresentsConditionValue;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Interfaces\RepresentsPreparedParameter;

class PreparedParameterCondition implements RepresentsConditionValue
{
	public function __construct( private readonly RepresentsColumn $column, private readonly RepresentsPreparedParameter $preparedParameter )
	{
	}

	public function getColumn(): RepresentsColumn
	{
		return $this->column;
	}

	public function getPreparedParameter(): ?RepresentsPreparedParameter
	{
		return $this->preparedParameter;
	}

	public function getValue(): ?RepresentsComparisonValue
	{
		return null;
	}
}
