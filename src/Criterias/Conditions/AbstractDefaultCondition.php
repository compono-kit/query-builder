<?php declare(strict_types=1);

namespace ComponoKit\QueryBuilder\Criterias\Conditions;

use ComponoKit\QueryBuilder\Exceptions\MissingValueException;
use ComponoKit\QueryBuilder\Models\Interfaces\RepresentsColumn;
use ComponoKit\QueryBuilder\Models\Interfaces\RepresentsComparisonValue;
use ComponoKit\QueryBuilder\Models\Interfaces\RepresentsCriteria;
use ComponoKit\QueryBuilder\Models\Interfaces\RepresentsPreparedParameter;
use ComponoKit\QueryBuilder\Models\Types\ComparisonOperator;

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
