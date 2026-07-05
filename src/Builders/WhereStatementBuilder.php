<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Builders;

use ComponoKit\Databases\Sql\QueryBuilder\Models\Interfaces\RepresentsCriteria;
use ComponoKit\Databases\Sql\QueryBuilder\Parsers\WhereClauseParser;

class WhereStatementBuilder
{
	/**
	 * @param RepresentsCriteria[] $criterias
	 */
	public function __construct( private readonly array $criterias )
	{
	}

	public static function fromSql( string $whereClause ): self
	{
		return new self( [ WhereClauseParser::parse( $whereClause ) ] );
	}

	public function buildWhereStatement(): string
	{
		$cleanedCriterias = [];
		foreach ( $this->criterias as $criteria )
		{
			$cleanedCriterias[] = $criteria->toString();
		}

		if ( $cleanedCriterias )
		{
			return sprintf( ' WHERE %s', implode( ' AND ', $cleanedCriterias ) );
		}

		return '';
	}

	public function getPreparedParams(): array
	{
		$params = [];
		foreach ( $this->criterias as $criteria )
		{
			foreach ( $criteria->getPreparedParameters() as $preparedParameter )
			{
				$params[ $preparedParameter->getName() ] = $preparedParameter->getValue();
			}
		}

		return $params;
	}
}
