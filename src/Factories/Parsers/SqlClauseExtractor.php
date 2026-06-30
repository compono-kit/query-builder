<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Factories\Parsers;

class SqlClauseExtractor
{
	private const CLAUSE_PATTERNS = [
		'where'   => '/\bWHERE\b/i',
		'groupBy' => '/\bGROUP\s+BY\b/i',
		'having'  => '/\bHAVING\b/i',
		'orderBy' => '/\bORDER\s+BY\b/i',
		'limit'   => '/\bLIMIT\b/i',
		'offset'  => '/\bOFFSET\b/i',
	];

	/**
	 * Splits a SQL string into its clause parts.
	 * Note: does not handle clause keywords appearing inside string literals.
	 *
	 * @return array{where: ?string, groupBy: ?string, having: ?string, orderBy: ?string, limit: ?string, offset: ?string}
	 */
	public static function extract(string $sql): array
	{
		$result = [
			'where'   => null,
			'groupBy' => null,
			'having'  => null,
			'orderBy' => null,
			'limit'   => null,
			'offset'  => null,
		];

		$positions = [];
		foreach (self::CLAUSE_PATTERNS as $key => $pattern)
		{
			if (preg_match($pattern, $sql, $matches, PREG_OFFSET_CAPTURE))
			{
				$positions[$key] = [
					'start'      => (int)$matches[0][1],
					'keywordLen' => strlen($matches[0][0]),
				];
			}
		}

		uasort($positions, fn(array $a, array $b) => $a['start'] <=> $b['start']);

		$keys      = array_keys($positions);
		$posValues = array_values($positions);
		$count     = count($keys);

		for ($i = 0; $i < $count; $i++)
		{
			$key          = $keys[$i];
			$contentStart = $posValues[$i]['start'] + $posValues[$i]['keywordLen'];
			$contentEnd   = isset($posValues[$i + 1]) ? $posValues[$i + 1]['start'] : strlen($sql);

			$content        = trim(substr($sql, $contentStart, $contentEnd - $contentStart));
			$result[$key]   = $content !== '' ? $content : null;
		}

		return $result;
	}
}
