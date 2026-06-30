<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Criterias\Conditions;

use ComponoKit\Databases\Sql\QueryBuilder\Exceptions\MissingValueException;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Interfaces\RepresentsColumn;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Interfaces\RepresentsCriteria;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Interfaces\RepresentsPreparedParameter;
use ComponoKit\Databases\Sql\QueryBuilder\Models\PreparedParameter;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Types\ComparisonOperator;

abstract class AbstractInCondition implements RepresentsCriteria
{
	private array             $preparedParameters = [];

	private ?string           $subQuery           = null;

	public function __construct( private readonly RepresentsColumn $column )
	{
		$this->guardRequirements();
		$this->init( $column, $this->getValues(), $this->getSubQuery() );
	}

	abstract protected function getComparisonOperator(): ComparisonOperator;

	abstract protected function getValues(): array;

	abstract protected function getSubQuery(): ?string;

	public function toString(): string
	{
		if ( $this->preparedParameters )
		{
			return sprintf( '%s %s (%s)', $this->column->toString(), $this->getComparisonOperator()->toString(), implode( ',', array_keys( $this->preparedParameters ) ) );
		}

		return sprintf( '%s %s (%s)', $this->column->toString(), $this->getComparisonOperator()->toString(), $this->subQuery );
	}

	/**
	 * @return RepresentsPreparedParameter[]
	 */
	public function getPreparedParameters(): array
	{
		return $this->preparedParameters;
	}

	/**
	 * @param RepresentsColumn     $column
	 * @param string[]|int[]|float[]|bool[] $values
	 * @param string|null                   $subQuery
	 *
	 * @return void
	 */
	private function init( RepresentsColumn $column, ?array $values, ?string $subQuery ): void
	{
		if ( $values )
		{
			$this->setPreparedParameters( $column, $values );
		}
		else
		{
			$this->subQuery = $subQuery;
		}
	}

	/**
	 * @param RepresentsColumn     $column
	 * @param string[]|int[]|float[]|bool[] $values
	 *
	 * @return void
	 */
	private function setPreparedParameters( RepresentsColumn $column, array $values ): void
	{
		foreach ( $values as $index => $value )
		{
			$this->preparedParameters[] = new PreparedParameter( sprintf( '%s_%d', $column->getColumnName()->getPureName(), $index ), (string)$value );
		}
	}

	private function guardRequirements(): void
	{
		if ( empty( $this->getValues() ) && null === $this->getSubQuery() )
		{
			throw new MissingValueException( 'You must either provide values or a sub query' );
		}
	}

	public function jsonSerialize(): string
	{
		return $this->toString();
	}
}
