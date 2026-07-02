<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Parsers;

use ComponoKit\Databases\Sql\QueryBuilder\Models\ExtractedClauses;

class SqlClauseExtractor
{
	private const CLAUSE_PATTERNS = [
		'select'  => '/\bSELECT\b/i',
		'from'    => '/\bFROM\b/i',
		'join'    => '/(?=\b(?:(?:INNER|LEFT(?:\s+OUTER)?|RIGHT(?:\s+OUTER)?|CROSS)\s+)?JOIN\b)/i',
		'where'   => '/\bWHERE\b/i',
		'groupBy' => '/\bGROUP\s+BY\b/i',
		'having'  => '/\bHAVING\b/i',
		'orderBy' => '/\bORDER\s+BY\b/i',
		'limit'   => '/\bLIMIT\b/i',
		'offset'  => '/\bOFFSET\b/i',
	];

	/**
	 * Splits a SQL string into its top-level clause parts.
	 * Keywords inside subqueries (parentheses) and string literals are ignored.
	 */
	public static function extract( string $sql ): ExtractedClauses
	{
		$result = [
			'select'  => null,
			'from'    => null,
			'join'    => null,
			'where'   => null,
			'groupBy' => null,
			'having'  => null,
			'orderBy' => null,
			'limit'   => null,
			'offset'  => null,
		];

		$depths    = ParenthesisDepth::compute( $sql );
		$positions = [];

		foreach ( self::CLAUSE_PATTERNS as $key => $pattern )
		{
			if ( !preg_match_all( $pattern, $sql, $matches, PREG_OFFSET_CAPTURE ) )
			{
				continue;
			}

			foreach ( $matches[0] as $match )
			{
				$matchPosition = (int)$match[1];

				if ( ( $depths[$matchPosition] ?? 0 ) === 0 )
				{
					$positions[$key] = [
						'start'      => $matchPosition,
						'keywordLen' => strlen( $match[0] ),
					];
					break;
				}
			}
		}

		uasort( $positions, fn( array $a, array $b ) => $a['start'] <=> $b['start'] );

		$keys      = array_keys( $positions );
		$posValues = array_values( $positions );
		$count     = count( $keys );

		for ( $i = 0; $i < $count; $i++ )
		{
			$key          = $keys[$i];
			$contentStart = $posValues[$i]['start'] + $posValues[$i]['keywordLen'];
			$contentEnd   = isset( $posValues[$i + 1] ) ? $posValues[$i + 1]['start'] : strlen( $sql );

			$content      = trim( substr( $sql, $contentStart, $contentEnd - $contentStart ) );
			$result[$key] = $content !== '' ? $content : null;
		}

		return new ExtractedClauses(
			$result['select'],
			$result['from'],
			$result['join'],
			$result['where'],
			$result['groupBy'],
			$result['having'],
			$result['orderBy'],
			$result['limit'],
			$result['offset']
		);
	}

}
