<?php declare(strict_types=1);

namespace ComponoKit\QueryBuilder\Criterias\Conditions;

use ComponoKit\QueryBuilder\Models\Interfaces\RepresentsColumn;
use ComponoKit\QueryBuilder\Models\Types\ComparisonOperator;

class NotInValuesCondition extends AbstractInCondition
{
	/**
	 * @param RepresentsColumn $column
	 * @param string[]|int[]|float[]    $values
	 */
	public function __construct( RepresentsColumn $column, private readonly array $values )
	{
		parent::__construct( $column );
	}

	protected function getComparisonOperator(): ComparisonOperator
	{
		return ComparisonOperator::notInOperator();
	}

	protected function getValues(): array
	{
		return $this->values;
	}

	protected function getSubQuery(): ?string
	{
		return null;
	}
}
