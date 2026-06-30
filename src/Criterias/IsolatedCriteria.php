<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Criterias;

use ComponoKit\Databases\Sql\QueryBuilder\Models\Interfaces\RepresentsCriteria;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Interfaces\RepresentsPreparedParameter;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Types\LogicalOperator;

class IsolatedCriteria implements RepresentsCriteria
{
	/** @var RepresentsCriteria[] */
	private array $criterias;

	public function __construct( private readonly LogicalOperator $logicalOperator, RepresentsCriteria...$criterias )
	{
		$this->criterias = $criterias;
	}

	public function toString(): string
	{
		return $this->criterias ? '(' . implode( ' ' . $this->logicalOperator->name . ' ', $this->getCriteriaStatements() ) . ')' : '';
	}

	private function getCriteriaStatements(): array
	{
		$criteriaStatements = [];
		foreach ( $this->criterias as $criteria )
		{
			$criteriaStatements[] = $criteria->toString();
		}

		return $criteriaStatements;
	}

	/**
	 * @return RepresentsPreparedParameter[]
	 */
	public function getPreparedParameters(): array
	{
		$preparedParameters = [];
		foreach ( $this->criterias as $criteria )
		{
			foreach ( $criteria->getPreparedParameters() as $preparedParameter )
			{
				$preparedParameters[] = $preparedParameter;
			}
		}

		return $preparedParameters;
	}

	public function jsonSerialize(): array
	{
		return $this->criterias;
	}
}
