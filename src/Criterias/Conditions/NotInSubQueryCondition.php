<?php declare(strict_types=1);

namespace ComponoKit\QueryBuilder\Criterias\Conditions;

use ComponoKit\QueryBuilder\Models\Interfaces\RepresentsColumn;
use ComponoKit\QueryBuilder\Models\Types\ComparisonOperator;

class NotInSubQueryCondition extends AbstractInCondition
{
	public function __construct( RepresentsColumn $column, private readonly string $subQuery )
	{
		parent::__construct( $column );
	}

	protected function getComparisonOperator(): ComparisonOperator
	{
		return ComparisonOperator::notInOperator();
	}

	protected function getValues(): array
	{
		return [];
	}

	protected function getSubQuery(): ?string
	{
		return $this->subQuery;
	}
}
