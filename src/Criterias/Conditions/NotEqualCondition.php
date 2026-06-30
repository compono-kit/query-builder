<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Criterias\Conditions;

use ComponoKit\Databases\Sql\QueryBuilder\Criterias\Conditions\Traits\InjectingConditionParams;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Types\ComparisonOperator;

class NotEqualCondition extends AbstractDefaultCondition
{
	use InjectingConditionParams;

	public function getComparisonOperator(): ComparisonOperator
	{
		return ComparisonOperator::notEqualOperator();
	}
}
