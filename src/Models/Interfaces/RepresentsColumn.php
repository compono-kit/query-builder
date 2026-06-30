<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Models\Interfaces;

interface RepresentsColumn extends \Stringable
{
	public function getTableName(): RepresentsTableName;

	public function getColumnName(): RepresentsColumnName;

	public function toString(): string;
}
