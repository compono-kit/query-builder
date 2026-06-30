<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Models;

use ComponoKit\Databases\Sql\QueryBuilder\Helpers\Quoter;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Interfaces\RepresentsColumn;

abstract class AbstractColumn implements RepresentsColumn
{
	public function toString(): string
	{
		if ( $this->getTableName()->getName() === '' )
		{
			return $this->getColumnName()->toString();
		}

		return Quoter::quote( $this->getTableName()->getName() ) . '.' . $this->getColumnName()->toString();
	}

	public function __toString(): string
	{
		return $this->toString();
	}
}
