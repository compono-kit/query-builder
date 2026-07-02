<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Parsers;

use ComponoKit\Databases\Sql\QueryBuilder\Exceptions\SqlParseException;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Interfaces\RepresentsJoinClause;
use ComponoKit\Databases\Sql\QueryBuilder\Models\JoinClause;
use ComponoKit\Databases\Sql\QueryBuilder\Models\SubqueryJoinSource;
use ComponoKit\Databases\Sql\QueryBuilder\Models\TableName;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Types\JoinType;

class JoinClauseParser
{
	private function __construct()
	{
	}

	/**
	 * @return RepresentsJoinClause[]
	 * @throws SqlParseException
	 */
	public static function parse( string $joinBlock ): array
	{
		$pattern = '/\b((?:LEFT(?:\s+OUTER)?|RIGHT(?:\s+OUTER)?|INNER|CROSS)\s+)?JOIN\b/i';

		preg_match_all( $pattern, $joinBlock, $matches, PREG_OFFSET_CAPTURE );

		if ( empty( $matches[0] ) )
		{
			throw new SqlParseException( sprintf( 'No JOIN keyword found in: "%s"', $joinBlock ) );
		}

		$depths        = ParenthesisDepth::compute( $joinBlock );
		$depth0Matches = [];

		foreach ( $matches[0] as $index => $match )
		{
			if ( ( $depths[(int)$match[1]] ?? 0 ) === 0 )
			{
				$depth0Matches[] = [
					'match'      => $match,
					'typePrefix' => trim( $matches[1][$index][0] ?? '' ),
				];
			}
		}

		if ( empty( $depth0Matches ) )
		{
			throw new SqlParseException( 'No top-level JOIN keyword found in join block' );
		}

		$joinClauses  = [];
		$totalMatches = count( $depth0Matches );

		for ( $index = 0; $index < $totalMatches; $index++ )
		{
			$currentMatch = $depth0Matches[$index]['match'];
			$typePrefix   = $depth0Matches[$index]['typePrefix'];

			$matchEnd   = (int)$currentMatch[1] + strlen( $currentMatch[0] );
			$segmentEnd = isset( $depth0Matches[$index + 1] )
				? (int)$depth0Matches[$index + 1]['match'][1]
				: strlen( $joinBlock );

			$segment       = trim( substr( $joinBlock, $matchEnd, $segmentEnd - $matchEnd ) );
			$joinClauses[] = self::parseSegment( $segment, $typePrefix );
		}

		return $joinClauses;
	}

	/**
	 * @throws SqlParseException
	 */
	private static function parseSegment( string $segment, string $typePrefix ): RepresentsJoinClause
	{
		if ( str_starts_with( $segment, '(' ) )
		{
			return self::parseSubquerySegment( $segment, $typePrefix );
		}

		$onCondition = null;
		$onMatch     = null;

		if ( preg_match( '/\bON\b/i', $segment, $onMatch, PREG_OFFSET_CAPTURE ) )
		{
			$onPosition      = (int)$onMatch[0][1];
			$tableDefinition = trim( substr( $segment, 0, $onPosition ) );
			$conditionText   = trim( substr( $segment, $onPosition + strlen( $onMatch[0][0] ) ) );
			$onCondition     = WhereClauseParser::parse( $conditionText );
		}
		else
		{
			$tableDefinition = $segment;
		}

		$tableName = self::parseTableDefinition( $tableDefinition );
		$joinType  = self::resolveJoinType( $typePrefix );

		return new JoinClause( $tableName, $joinType, $onCondition );
	}

	/**
	 * @throws SqlParseException
	 */
	private static function parseSubquerySegment( string $segment, string $typePrefix ): RepresentsJoinClause
	{
		$depth           = 0;
		$length          = strlen( $segment );
		$closingPosition = null;

		for ( $i = 0; $i < $length; $i++ )
		{
			if ( $segment[$i] === '(' )
			{
				$depth++;
			}
			elseif ( $segment[$i] === ')' )
			{
				$depth--;

				if ( $depth === 0 )
				{
					$closingPosition = $i;
					break;
				}
			}
		}

		if ( $closingPosition === null )
		{
			throw new SqlParseException( 'Unmatched parenthesis in JOIN subquery' );
		}

		$subquerySql = trim( substr( $segment, 1, $closingPosition - 1 ) );
		$remainder   = trim( substr( $segment, $closingPosition + 1 ) );

		$alias       = null;
		$onCondition = null;

		if ( preg_match( '/\bON\b/i', $remainder, $onMatch, PREG_OFFSET_CAPTURE ) )
		{
			$onPosition    = (int)$onMatch[0][1];
			$aliasPart     = trim( substr( $remainder, 0, $onPosition ) );
			$conditionText = trim( substr( $remainder, $onPosition + strlen( $onMatch[0][0] ) ) );
			$onCondition   = WhereClauseParser::parse( $conditionText );

			if ( $aliasPart !== '' )
			{
				$alias = trim( preg_replace( '/^AS\s+/i', '', $aliasPart ), '`' );
			}
		}
		elseif ( $remainder !== '' )
		{
			$alias = trim( preg_replace( '/^AS\s+/i', '', $remainder ), '`' );
		}

		$joinType = self::resolveJoinType( $typePrefix );

		return new JoinClause( new SubqueryJoinSource( $subquerySql, $alias ), $joinType, $onCondition );
	}

	/**
	 * @throws SqlParseException
	 */
	private static function parseTableDefinition( string $tableDefinition ): TableName
	{
		$pattern = '/^`?([a-zA-Z_][a-zA-Z0-9_]*)`?(?:\s+(?:AS\s+)?`?([a-zA-Z_][a-zA-Z0-9_]*)`?)?$/i';

		if ( !preg_match( $pattern, trim( $tableDefinition ), $tableMatches ) )
		{
			throw new SqlParseException( sprintf( 'Invalid JOIN table definition: "%s"', $tableDefinition ) );
		}

		$alias = isset( $tableMatches[2] ) && $tableMatches[2] !== '' ? $tableMatches[2] : null;

		return new TableName( $tableMatches[1], $alias );
	}

	private static function resolveJoinType( string $typePrefix ): JoinType
	{
		$normalized = strtoupper( preg_replace( '/\s+/', ' ', trim( $typePrefix ) ) );

		return match ( true )
		{
			str_starts_with( $normalized, 'LEFT' )  => JoinType::LEFT,
			str_starts_with( $normalized, 'RIGHT' ) => JoinType::RIGHT,
			$normalized === 'CROSS'                 => JoinType::CROSS,
			default                                 => JoinType::INNER,
		};
	}
}
