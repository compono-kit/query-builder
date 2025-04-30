<?php declare(strict_types=1);

namespace ComponoKit\QueryBuilder\Models;

use ComponoKit\QueryBuilder\Helpers\Quoter;
use ComponoKit\QueryBuilder\Models\Interfaces\RepresentsTableName;

class TableName implements RepresentsTableName
{
	public function __construct( private readonly string $name, private readonly ?string $alias = null )
	{
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function getAlias(): ?string
	{
		return $this->alias;
	}

	public function hasAlias(): bool
	{
		return null !== $this->alias;
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
