<?php declare(strict_types=1);

namespace ComponoKit\QueryBuilder\Builders;

use ComponoKit\QueryBuilder\Models\Interfaces\RepresentsOrderBy;

class OrderByBuilder
{
	private function __construct()
	{
	}

	/**
	 * @param RepresentsOrderBy[] $orderByList
	 *
	 * @return string
	 */
	public static function build( array $orderByList ): string
	{
		if ( $orderByList )
		{
			$columnDirections = [];
			foreach ( $orderByList as $orderBy )
			{
				$columnDirections[] = sprintf( '%s %s', $orderBy->getColumn()->toString(), $orderBy->getDirection()->name );
			}

			return ' ORDER BY ' . implode( ', ', $columnDirections );
		}

		return '';
	}
}
