<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Helpers;

use ComponoKit\Databases\Sql\QueryBuilder\Models\ColumnName;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Interfaces\RepresentsColumnName;

class ColumnNames
{
	/**
	 * @param string[] $columnNameStrings
	 *
	 * @return RepresentsColumnName[]
	 */
	public static function toColumnNames( array $columnNameStrings ): array
	{
		$columnNames = [];
		foreach ( $columnNameStrings as $columnNameString )
		{
			$columnNameSplit = explode( ':', $columnNameString );
			$columnNames[]   = isset( $columnNameSplit[1] ) ? new ColumnName( $columnNameSplit[0], $columnNameSplit[1] ) : new ColumnName( $columnNameSplit[0] );
		}

		return $columnNames;
	}

	/**
	 * @param string                 $tableAliasName
	 * @param RepresentsColumnName[] $columnNames
	 *
	 * @return array
	 */
	public static function toSelectableColumns( string $tableAliasName, array $columnNames ): array
	{
		$selectableColumns = [];
		foreach ( $columnNames as $columnName )
		{
			$selectableColumns[] = sprintf( '%s.%s', $tableAliasName, $columnName->toString() );
		}

		return $selectableColumns;
	}
}
