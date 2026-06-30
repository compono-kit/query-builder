<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Builders;

use ComponoKit\Databases\Sql\QueryBuilder\Models\Interfaces\RepresentsColumn;

class GroupByBuilder
{
	private function __construct()
	{
	}

	/**
	 * @param RepresentsColumn[] $columns
	 *
	 * @return string
	 */
	public static function build( array $columns ): string
	{
		if ( $columns )
		{
			$columnStrings = [];
			foreach ( $columns as $column )
			{
				$columnStrings[] = $column->toString();
			}

			return ' GROUP BY ' . implode( ', ', $columnStrings );
		}

		return '';
	}
}
