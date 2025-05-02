<?php declare(strict_types=1);

namespace ComponoKit\QueryBuilder\Models;

use ComponoKit\QueryBuilder\Models\Interfaces\RepresentsColumn;

abstract class AbstractColumn implements RepresentsColumn
{
	public function toString(): string
	{
		return $this->getTableName()->toString() . '.' . $this->getColumnName()->toString();
	}

	public function __toString(): string
	{
		return $this->toString();
	}
}
