<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Parsers;

use ComponoKit\Databases\Sql\QueryBuilder\Exceptions\SqlParseException;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Limit;

class LimitClauseParser
{
	/**
	 * Supports:
	 *   LIMIT count              → Limit(count, 0)
	 *   LIMIT count OFFSET offset → two separate clauses, already extracted
	 *   LIMIT offset, count      → MySQL comma syntax as produced by LimitBuilder
	 *
	 * @throws SqlParseException
	 */
	public static function parse(string $limitClause, ?string $offsetClause): Limit
	{
		$limitClause = trim($limitClause);

		// MySQL comma syntax: LIMIT offset, count
		if (preg_match('/^(\d+)\s*,\s*(\d+)$/', $limitClause, $matches))
		{
			$offset = (int)$matches[1];
			$count  = (int)$matches[2];

			return new Limit($count, $offset);
		}

		if (!preg_match('/^\d+$/', $limitClause))
		{
			throw new SqlParseException(sprintf('Invalid LIMIT clause: "%s"', $limitClause));
		}

		$count  = (int)$limitClause;
		$offset = 0;

		if ($offsetClause !== null)
		{
			$offsetClause = trim($offsetClause);
			if (!preg_match('/^\d+$/', $offsetClause))
			{
				throw new SqlParseException(sprintf('Invalid OFFSET clause: "%s"', $offsetClause));
			}
			$offset = (int)$offsetClause;
		}

		return new Limit($count, $offset);
	}
}
