<?php declare(strict_types=1);

namespace ComponoKit\QueryBuilder\Models;

use ComponoKit\QueryBuilder\Models\Interfaces\RepresentsColumn;
use ComponoKit\QueryBuilder\Models\Interfaces\RepresentsColumnName;
use ComponoKit\QueryBuilder\Models\Interfaces\RepresentsTableName;

class Column implements RepresentsColumn
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

	public function toString(): string
	{
		return $this->tableName->toString() . '.' . $this->columnName->toString();
	}

	public function __toString(): string
	{
		return $this->toString();
	}
}
