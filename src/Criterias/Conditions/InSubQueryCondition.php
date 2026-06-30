<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Criterias\Conditions;

use ComponoKit\Databases\Sql\QueryBuilder\Models\Interfaces\RepresentsColumn;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Types\ComparisonOperator;

class InSubQueryCondition extends AbstractInCondition
{
	public function __construct( RepresentsColumn $column, private readonly string $subQuery )
	{
		parent::__construct( $column );
	}

	protected function getComparisonOperator(): ComparisonOperator
	{
		return ComparisonOperator::inOperator();
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
