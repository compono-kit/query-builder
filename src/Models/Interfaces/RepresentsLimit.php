<?php declare(strict_types=1);

namespace ComponoKit\QueryBuilder\Models\Interfaces;

interface RepresentsLimit
{
	public function getOffset(): int;

	public function getCount(): int;
}
