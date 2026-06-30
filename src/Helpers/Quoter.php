<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Helpers;

class Quoter
{
	private function __construct()
	{

	}

	public static function quoteColumn( string $column ): string
	{
		$columnIds = [];

		foreach ( explode( '.', $column, 2 ) as $columnId )
		{
			$columnIds[] = self::quote( str_replace( "`", "``", $columnId ) );
		}

		return implode( '.', $columnIds );
	}

	public static function quoteMultipleColumns( array $columns ): array
	{
		$quotedColumns = [];
		foreach ( $columns as $column )
		{
			$quotedColumns[] = self::quoteColumn( $column );
		}

		return $quotedColumns;
	}

	public static function quote( string $value ): string
	{
		return sprintf( '`%s`', $value );
	}
}
