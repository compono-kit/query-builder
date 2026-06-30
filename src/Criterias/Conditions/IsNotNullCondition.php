<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Criterias\Conditions;

use ComponoKit\Databases\Sql\QueryBuilder\Models\Types\ComparisonOperator;

class IsNotNullCondition extends AbstractNullCondition
{
	protected function getComparisonOperator(): ComparisonOperator
	{
		return ComparisonOperator::isNotNullOperator();
	}
}
