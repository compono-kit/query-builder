<?php declare(strict_types=1);

namespace ComponoKit\QueryBuilder\Criterias\Conditions;

use ComponoKit\QueryBuilder\Models\Types\ComparisonOperator;

class IsNotNullCondition extends AbstractNullCondition
{
	protected function getComparisonOperator(): ComparisonOperator
	{
		return ComparisonOperator::isNotNullOperator();
	}
}
