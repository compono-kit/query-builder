<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Criterias;

use ComponoKit\Databases\Sql\QueryBuilder\Models\Interfaces\RepresentsCriteria;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Interfaces\RepresentsPreparedParameter;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Types\LogicalOperator;

class Criteria implements RepresentsCriteria
{
	/** @var RepresentsCriteria[] */
	private array $criterias;

	public function __construct( private readonly LogicalOperator $logicalOperator, RepresentsCriteria...$criterias )
	{
		$this->criterias = $criterias;
	}

	public function addCriteria( RepresentsCriteria...$criterias ): self
	{
		$totalCriterias = array_merge( $this->criterias, $criterias );

		return new self( $this->logicalOperator, ...$totalCriterias );
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

	public function toString(): string
	{
		$criterias = [];
		foreach ( $this->criterias as $criteria )
		{
			$criterias[] = $criteria->toString();
		}

		return $criterias ? implode( ' ' . $this->logicalOperator->name . ' ', $criterias ) : '';
	}

	public function getLogicalOperator(): LogicalOperator
	{
		return $this->logicalOperator;
	}

	/** @return RepresentsCriteria[] */
	public function getCriterias(): array
	{
		return $this->criterias;
	}

	public function jsonSerialize(): array
	{
		return $this->criterias;
	}
}
