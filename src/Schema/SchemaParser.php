<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Schema;

class SchemaParser
{
	private const CONSTRAINT_KEYWORDS = ['PRIMARY', 'INDEX', 'KEY', 'UNIQUE', 'FOREIGN', 'CONSTRAINT', 'CHECK'];

	private function __construct()
	{
	}

	public static function fromFile( string $path ): SchemaRegistry
	{
		return self::fromSql( file_get_contents( $path ) );
	}

	public static function fromSql( string $sql ): SchemaRegistry
	{
		$definitions = [];

		$pattern = '/CREATE\s+(?:TEMPORARY\s+)?TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?[`"]?(\w[\w\-]*)[`"]?\s*\(/i';

		if ( !preg_match_all( $pattern, $sql, $matches, PREG_OFFSET_CAPTURE ) )
		{
			return SchemaRegistry::fromDefinitions( [] );
		}

		foreach ( $matches[0] as $index => $match )
		{
			$tableName  = $matches[1][$index][0];
			$bodyStart  = $match[1] + strlen( $match[0] );
			$body       = self::extractParenthesisBody( $sql, $bodyStart - 1 );
			$columns    = self::extractColumnNames( $body );
			$definitions[] = new TableDefinition( $tableName, $columns );
		}

		return SchemaRegistry::fromDefinitions( $definitions );
	}

	private static function extractParenthesisBody( string $sql, int $openParenPos ): string
	{
		$depth  = 0;
		$start  = null;
		$length = strlen( $sql );

		for ( $i = $openParenPos; $i < $length; $i++ )
		{
			if ( $sql[$i] === '(' )
			{
				$depth++;

				if ( $depth === 1 )
				{
					$start = $i + 1;
				}
			}
			elseif ( $sql[$i] === ')' )
			{
				$depth--;

				if ( $depth === 0 )
				{
					return substr( $sql, $start, $i - $start );
				}
			}
		}

		return '';
	}

	private static function extractColumnNames( string $body ): array
	{
		$columns = [];
		$entries = self::splitByTopLevelCommas( $body );

		foreach ( $entries as $entry )
		{
			$entry = trim( $entry );

			if ( $entry === '' )
			{
				continue;
			}

			$firstWord = strtoupper( preg_replace( '/^[`"]?(\w+)[`"]?.*$/s', '$1', $entry ) );

			if ( in_array( $firstWord, self::CONSTRAINT_KEYWORDS, true ) )
			{
				continue;
			}

			$columnName = preg_replace( '/^[`"]?(\w[\w\-]*)[`"]?.*$/s', '$1', $entry );

			if ( $columnName !== '' )
			{
				$columns[] = $columnName;
			}
		}

		return $columns;
	}

	private static function splitByTopLevelCommas( string $body ): array
	{
		$parts  = [];
		$depth  = 0;
		$current = '';
		$length = strlen( $body );

		for ( $i = 0; $i < $length; $i++ )
		{
			$char = $body[$i];

			if ( $char === '(' )
			{
				$depth++;
				$current .= $char;
			}
			elseif ( $char === ')' )
			{
				$depth--;
				$current .= $char;
			}
			elseif ( $char === ',' && $depth === 0 )
			{
				$parts[] = $current;
				$current = '';
			}
			else
			{
				$current .= $char;
			}
		}

		if ( $current !== '' )
		{
			$parts[] = $current;
		}

		return $parts;
	}
}
