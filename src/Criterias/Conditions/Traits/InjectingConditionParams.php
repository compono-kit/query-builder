<?php declare(strict_types=1);

namespace ComponoKit\QueryBuilder\Criterias\Conditions\Traits;

use ComponoKit\QueryBuilder\Models\Interfaces\RepresentsColumn;
use ComponoKit\QueryBuilder\Models\Interfaces\RepresentsComparisonValue;
use ComponoKit\QueryBuilder\Models\Interfaces\RepresentsConditionValue;
use ComponoKit\QueryBuilder\Models\Interfaces\RepresentsPreparedParameter;

trait InjectingConditionParams
{
	private RepresentsColumn             $column;

	private ?RepresentsPreparedParameter $preparedParameter;

	private ?RepresentsComparisonValue   $value;

	public function __construct( RepresentsConditionValue $conditionValue )
	{
		$this->column            = $conditionValue->getColumn();
		$this->preparedParameter = $conditionValue->getPreparedParameter();
		$this->value             = $conditionValue->getValue();
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
		return $this->value;
	}
}
