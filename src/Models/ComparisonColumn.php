<?php declare(strict_types=1);

namespace ComponoKit\QueryBuilder\Models;

use ComponoKit\QueryBuilder\Models\Interfaces\RepresentsColumn;
use ComponoKit\QueryBuilder\Models\Interfaces\RepresentsComparisonValue;

class ComparisonColumn implements RepresentsComparisonValue
{
	public function __construct( private readonly RepresentsColumn $column )
	{
	}

	public function isColumn(): bool
	{
		return true;
	}

	public function toRawType(): int|string|float
	{
		return $this->column->toString();
	}

	public function toString(): string
	{
		return (string)$this->toRawType();
	}

	public function __toString(): string
	{
		return $this->toString();
	}

	public function jsonSerialize(): string|int|float
	{
		return $this->toString();
	}
}
