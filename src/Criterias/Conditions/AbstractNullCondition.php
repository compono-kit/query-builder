<?php declare(strict_types=1);

namespace ComponoKit\QueryBuilder\Criterias\Conditions;

use ComponoKit\QueryBuilder\Models\Interfaces\RepresentsColumn;
use ComponoKit\QueryBuilder\Models\Interfaces\RepresentsCriteria;
use ComponoKit\QueryBuilder\Models\Types\ComparisonOperator;

abstract class AbstractNullCondition implements RepresentsCriteria
{
	public function __construct( private readonly RepresentsColumn $column )
	{
	}

	abstract protected function getComparisonOperator(): ComparisonOperator;

	public function toString(): string
	{
		return sprintf(
			'%s %s',
			$this->column->toString(),
			$this->getComparisonOperator()->toString()
		);
	}

	public function getPreparedParameters(): array
	{
		return [];
	}

	public function jsonSerialize(): string
	{
		return $this->toString();
	}
}
