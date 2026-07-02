<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Helpers;

class Quoter
{
	private function __construct()
	{

	}

	public static function quoteColumn( string $column ): string
	{
		$parts = [];

		foreach ( explode( '.', $column, 2 ) as $part )
		{
			$parts[] = self::quote( $part );
		}

		return implode( '.', $parts );
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
		if ( preg_match( '/^[a-zA-Z_][a-zA-Z0-9_]*$/', $value ) )
		{
			return $value;
		}

		return sprintf( '`%s`', str_replace( '`', '``', $value ) );
	}
}
