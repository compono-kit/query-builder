<?php declare(strict_types=1);

namespace ComponoKit\QueryBuilder\Models\Interfaces;

interface RepresentsComparisonValue extends \Stringable, \JsonSerializable
{
	public function isColumn(): bool;

	public function toRawType(): int|string|float;

	public function toString(): string;

	public function jsonSerialize(): string|int|float;
}
