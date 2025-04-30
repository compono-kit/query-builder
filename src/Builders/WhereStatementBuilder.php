<?php declare(strict_types=1);

namespace ComponoKit\QueryBuilder\Builders;

use ComponoKit\QueryBuilder\Models\Interfaces\RepresentsCriteria;

class WhereStatementBuilder
{
	private function __construct()
	{
	}

	/**
	 * @param RepresentsCriteria[] $criterias
	 *
	 * @return string
	 */
	public static function buildWhereStatement( array $criterias ): string
	{
		$cleanedCriterias = [];
		foreach ( $criterias as $criteria )
		{
			$cleanedCriterias[] = $criteria->toString();
		}

		if ( $cleanedCriterias )
		{
			return sprintf( ' WHERE %s', implode( ' AND ', $cleanedCriterias ) );
		}

		return ' WHERE 1';
	}

	/**
	 * @param RepresentsCriteria[] $criterias
	 *
	 * @return array
	 */
	public static function getPreparedParams( array $criterias ): array
	{
		$params = [];
		foreach ( $criterias as $criteria )
		{
			foreach ( $criteria->getPreparedParameters() as $preparedParameter )
			{
				$params[ $preparedParameter->getName() ] = $preparedParameter->getValue();
			}
		}

		return $params;
	}
}
