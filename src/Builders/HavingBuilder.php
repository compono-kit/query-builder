<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Builders;

use ComponoKit\Databases\Sql\QueryBuilder\Models\Interfaces\RepresentsCriteria;

class HavingBuilder
{
	private function __construct()
	{
	}

	/**
	 * @param RepresentsCriteria[] $criterias
	 */
	public static function build( array $criterias ): string
	{
		$statements = [];
		foreach ( $criterias as $criteria )
		{
			$statements[] = $criteria->toString();
		}

		if ( $statements )
		{
			return sprintf( ' HAVING %s', implode( ' AND ', $statements ) );
		}

		return '';
	}
}
