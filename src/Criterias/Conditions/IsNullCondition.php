<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Criterias\Conditions;

use ComponoKit\Databases\Sql\QueryBuilder\Models\Types\ComparisonOperator;

class IsNullCondition extends AbstractNullCondition
{
	protected function getComparisonOperator(): ComparisonOperator
	{
		return ComparisonOperator::isNullOperator();
	}
}
