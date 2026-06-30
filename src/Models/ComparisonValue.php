<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Models;

use ComponoKit\Databases\Sql\QueryBuilder\Models\Interfaces\RepresentsComparisonValue;

class ComparisonValue implements RepresentsComparisonValue
{
	public function __construct( private readonly int|string|float $value )
	{
	}

	public function isColumn(): bool
	{
		return false;
	}

	public function toRawType(): int|string|float
	{
		return $this->value;
	}

	public function toString(): string
	{
		return (string)$this->value;
	}

	public function __toString(): string
	{
		return $this->toString();
	}

	public function jsonSerialize(): string|int|float
	{
		return $this->value;
	}
}
