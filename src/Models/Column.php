<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Models;

use ComponoKit\Databases\Sql\QueryBuilder\Models\Interfaces\RepresentsColumnName;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Interfaces\RepresentsTableName;

class Column extends AbstractColumn
{
	public function __construct( private readonly RepresentsTableName $tableName, private readonly RepresentsColumnName $columnName )
	{
	}

	public function getTableName(): RepresentsTableName
	{
		return $this->tableName;
	}

	public function getColumnName(): RepresentsColumnName
	{
		return $this->columnName;
	}
}
