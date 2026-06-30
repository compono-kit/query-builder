<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Criterias\Conditions;

use ComponoKit\Databases\Sql\QueryBuilder\Models\Interfaces\RepresentsColumn;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Types\ComparisonOperator;

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
