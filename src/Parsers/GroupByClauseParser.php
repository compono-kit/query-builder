<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Parsers;

use ComponoKit\Databases\Sql\QueryBuilder\Exceptions\SqlParseException;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Column;
use ComponoKit\Databases\Sql\QueryBuilder\Models\ColumnName;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Interfaces\RepresentsColumn;
use ComponoKit\Databases\Sql\QueryBuilder\Models\TableName;

class GroupByClauseParser
{
	/**
	 * @return RepresentsColumn[]
	 * @throws SqlParseException
	 */
	public static function parse(string $groupByClause): array
	{
		$columns = [];
		$parts   = array_map('trim', explode(',', $groupByClause));

		foreach ($parts as $part)
		{
			if ($part === '')
			{
				continue;
			}

			$columns[] = self::parseColumn($part);
		}

		return $columns;
	}

	private static function parseColumn(string $part): RepresentsColumn
	{
		$part = preg_replace('/`([^`]*)`/', '$1', trim($part));

		if (!preg_match('/^([a-zA-Z_][a-zA-Z0-9_]*)(?:\.([a-zA-Z_][a-zA-Z0-9_]*))?$/', $part, $matches))
		{
			throw new SqlParseException(sprintf('Invalid GROUP BY column: "%s"', $part));
		}

		if (isset($matches[2]) && $matches[2] !== '')
		{
			return new Column(new TableName($matches[1]), new ColumnName($matches[2]));
		}

		return new Column(new TableName(''), new ColumnName($matches[1]));
	}
}
