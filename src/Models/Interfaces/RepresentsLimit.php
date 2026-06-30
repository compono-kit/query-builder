<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Models\Interfaces;

interface RepresentsLimit
{
	public function getOffset(): int;

	public function getCount(): int;
}
