<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Models\Interfaces;

interface RepresentsColumnName extends \Stringable, \JsonSerializable
{
	public function getPureName(): string;

	public function hasAlias(): bool;

	public function getAlias(): string;

	public function toString(): string;
}
