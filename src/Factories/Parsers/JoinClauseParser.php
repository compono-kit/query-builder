<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Factories\Parsers;

use ComponoKit\Databases\Sql\QueryBuilder\Factories\Exceptions\SqlParseException;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Interfaces\RepresentsJoinClause;
use ComponoKit\Databases\Sql\QueryBuilder\Models\JoinClause;
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

		$joinClauses = [];
		$totalMatches = count( $matches[0] );

		for ( $index = 0; $index < $totalMatches; $index++ )
		{
			$matchEnd   = $matches[0][$index][1] + strlen( $matches[0][$index][0] );
			$segmentEnd = isset( $matches[0][$index + 1] ) ? $matches[0][$index + 1][1] : strlen( $joinBlock );
			$segment    = trim( substr( $joinBlock, $matchEnd, $segmentEnd - $matchEnd ) );
			$typePrefix = trim( $matches[1][$index][0] ?? '' );

			$joinClauses[] = self::parseSegment( $segment, $typePrefix );
		}

		return $joinClauses;
	}

	/**
	 * @throws SqlParseException
	 */
	private static function parseSegment( string $segment, string $typePrefix ): RepresentsJoinClause
	{
		$onPosition = null;

		if ( preg_match( '/\bON\b/i', $segment, $onMatch, PREG_OFFSET_CAPTURE ) )
		{
			$onPosition = (int)$onMatch[0][1];
		}

		if ( $onPosition !== null )
		{
			$tableDefinition = trim( substr( $segment, 0, $onPosition ) );
			$conditionText   = trim( substr( $segment, $onPosition + strlen( $onMatch[0][0] ) ) );
			$onCondition     = WhereClauseParser::parse( $conditionText );
		}
		else
		{
			$tableDefinition = $segment;
			$onCondition     = null;
		}

		$tableName = self::parseTableDefinition( $tableDefinition );
		$joinType  = self::resolveJoinType( $typePrefix );

		return new JoinClause( $tableName, $joinType, $onCondition );
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
