<?php declare(strict_types=1);

namespace ComponoKit\QueryBuilder\Criterias\Conditions;

use ComponoKit\QueryBuilder\Criterias\Conditions\Traits\InjectingConditionParams;
use ComponoKit\QueryBuilder\Models\Types\ComparisonOperator;

class EqualCondition extends AbstractDefaultCondition
{
	use InjectingConditionParams;

	public function getComparisonOperator(): ComparisonOperator
	{
		return ComparisonOperator::equalOperator();
	}
}
