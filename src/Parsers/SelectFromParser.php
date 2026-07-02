<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Parsers;

use ComponoKit\Databases\Sql\QueryBuilder\Builders\SelectBuilder;
use ComponoKit\Databases\Sql\QueryBuilder\Exceptions\SqlParseException;

class SelectFromParser
{
	private function __construct()
	{
	}

	/**
	 * Builds the query head (SELECT ... FROM ...) from the extracted raw clauses.
	 *
	 * @throws SqlParseException
	 */
	public static function parse( ?string $selectClause, ?string $fromClause ): SelectBuilder
	{
		if ( $fromClause === null || trim( $fromClause ) === '' )
		{
			throw new SqlParseException( 'Cannot build a SELECT statement without a FROM clause.' );
		}

		$selectBuilder = self::applyFrom( SelectBuilder::create(), $fromClause );

		return self::applySelect( $selectBuilder, $selectClause );
	}

	/**
	 * @throws SqlParseException
	 */
	private static function applyFrom( SelectBuilder $selectBuilder, string $fromClause ): SelectBuilder
	{
		$pattern = '/^`?([a-zA-Z_][a-zA-Z0-9_]*)`?(?:\s+(?:AS\s+)?`?([a-zA-Z_][a-zA-Z0-9_]*)`?)?$/i';

		if ( !preg_match( $pattern, trim( $fromClause ), $matches ) )
		{
			throw new SqlParseException( sprintf( 'Invalid FROM clause: "%s"', $fromClause ) );
		}

		$alias = isset( $matches[2] ) && $matches[2] !== '' ? $matches[2] : null;

		return $selectBuilder->from( $matches[1], $alias );
	}

	private static function applySelect( SelectBuilder $selectBuilder, ?string $selectClause ): SelectBuilder
	{
		if ( $selectClause === null || trim( $selectClause ) === '' )
		{
			return $selectBuilder;
		}

		$expressions = self::splitTopLevelCommas( $selectClause );

		if ( $expressions === [] )
		{
			return $selectBuilder;
		}

		return $selectBuilder->select( $expressions );
	}

	/**
	 * @return string[]
	 */
	private static function splitTopLevelCommas( string $input ): array
	{
		$depths = ParenthesisDepth::compute( $input );
		$parts  = [];
		$buffer = '';
		$length = strlen( $input );

		for ( $index = 0; $index < $length; $index++ )
		{
			$character = $input[$index];

			if ( $character === ',' && ( $depths[$index] ?? 0 ) === 0 )
			{
				self::appendNonEmpty( $parts, $buffer );
				$buffer = '';
				continue;
			}

			$buffer .= $character;
		}

		self::appendNonEmpty( $parts, $buffer );

		return $parts;
	}

	/**
	 * @param string[] $parts
	 */
	private static function appendNonEmpty( array &$parts, string $buffer ): void
	{
		$trimmed = trim( $buffer );

		if ( $trimmed !== '' )
		{
			$parts[] = $trimmed;
		}
	}
}
