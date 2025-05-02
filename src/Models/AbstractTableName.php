<?php declare(strict_types=1);

namespace ComponoKit\QueryBuilder\Models;

use ComponoKit\QueryBuilder\Models\Interfaces\RepresentsTableName;

abstract class AbstractTableName implements RepresentsTableName
{
	public function hasAlias(): bool
	{
		return null !== $this->getAlias();
	}

	public function toString(): string
	{
		return sprintf(
			'%s%s',
			$this->getName(),
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
