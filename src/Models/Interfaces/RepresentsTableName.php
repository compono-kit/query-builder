<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Models\Interfaces;

interface RepresentsTableName extends \Stringable, \JsonSerializable
{
	public function getName(): string;

	public function getAlias(): ?string;

	public function hasAlias(): bool;

	public function toString(): string;
}
