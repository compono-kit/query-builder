<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Criterias\Conditions;

use ComponoKit\Databases\Sql\QueryBuilder\Exceptions\MissingValueException;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Interfaces\RepresentsColumn;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Interfaces\RepresentsComparisonValue;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Interfaces\RepresentsCriteria;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Interfaces\RepresentsPreparedParameter;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Types\ComparisonOperator;

abstract class AbstractDefaultCondition implements RepresentsCriteria
{
	abstract protected function getColumn(): RepresentsColumn;

	abstract protected function getComparisonOperator(): ComparisonOperator;

	abstract protected function getPreparedParameter(): ?RepresentsPreparedParameter;

	abstract protected function getValue(): ?RepresentsComparisonValue;

	public function getPreparedParameters(): array
	{
		return null !== $this->getPreparedParameter() ? [ $this->getPreparedParameter() ] : [];
	}

	public function toString(): string
	{
		if ( null === $this->getPreparedParameter() && null === $this->getValue() )
		{
			throw new MissingValueException( 'You must either provide a prepared parameter or a value' );
		}

		if ( null !== $this->getPreparedParameter() )
		{
			return sprintf(
				'%s %s :%s',
				$this->getColumn()->toString(),
				$this->getComparisonOperator()->toString(),
				$this->getPreparedParameter()->getName()
			);
		}

		return sprintf(
			'%s %s %s',
			$this->getColumn()->toString(),
			$this->getComparisonOperator()->toString(),
			$this->getValue()->isColumn() || !is_string( $this->getValue()->toRawType() ) ? $this->getValue()->toRawType() : "'" . $this->getValue()->toRawType() . "'"
		);
	}

	public function jsonSerialize(): string
	{
		return $this->toString();
	}
}
