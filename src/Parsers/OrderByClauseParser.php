<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Parsers;

use ComponoKit\Databases\Sql\QueryBuilder\Exceptions\SqlParseException;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Column;
use ComponoKit\Databases\Sql\QueryBuilder\Models\ColumnName;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Interfaces\RepresentsOrderBy;
use ComponoKit\Databases\Sql\QueryBuilder\Models\OrderBy;
use ComponoKit\Databases\Sql\QueryBuilder\Models\TableName;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Types\OrderDirection;

class OrderByClauseParser
{
	/**
	 * @return RepresentsOrderBy[]
	 * @throws SqlParseException
	 */
	public static function parse(string $orderByClause): array
	{
		$orderByList = [];
		$parts       = array_map('trim', explode(',', $orderByClause));

		foreach ($parts as $part)
		{
			if ($part === '')
			{
				continue;
			}

			$orderByList[] = self::parseSingle($part);
		}

		return $orderByList;
	}

	private static function parseSingle(string $part): RepresentsOrderBy
	{
		// Strip backticks for identifiers produced by ColumnName::toString()
		$part = preg_replace('/`([^`]*)`/', '$1', trim($part));

		if (!preg_match('/^([a-zA-Z_][a-zA-Z0-9_]*)(?:\.([a-zA-Z_][a-zA-Z0-9_]*))?(?:\s+(ASC|DESC))?$/i', $part, $matches))
		{
			throw new SqlParseException(sprintf('Invalid ORDER BY part: "%s"', $part));
		}

		$first     = $matches[1];
		$second    = $matches[2] ?? '';
		$direction = OrderDirection::from(strtoupper($matches[3] ?? 'ASC'));

		if ($second !== '')
		{
			$column = new Column(new TableName($first), new ColumnName($second));
		}
		else
		{
			$column = new Column(new TableName(''), new ColumnName($first));
		}

		return new OrderBy($column, $direction);
	}
}
