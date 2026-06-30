<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Models;

use ComponoKit\Databases\Sql\QueryBuilder\Helpers\Quoter;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Interfaces\RepresentsColumnName;

class ColumnName implements RepresentsColumnName
{
	public function __construct( private readonly string $name, private readonly ?string $alias = null )
	{
	}

	public function getPureName(): string
	{
		return $this->name;
	}

	public function hasAlias(): bool
	{
		return null !== $this->alias;
	}

	public function getAlias(): string
	{
		return $this->alias;
	}

	public function toString(): string
	{
		return sprintf(
			'%s%s',
			Quoter::quoteColumn( $this->name ),
			$this->hasAlias() ? ' ' . $this->getAlias() : ''
		);
	}

	public function __toString(): string
	{
		return $this->toString();
	}

	public function jsonSerialize(): string
	{
		return $this->toString();
	}
}
