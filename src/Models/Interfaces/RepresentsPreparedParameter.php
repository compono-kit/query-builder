<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Models\Interfaces;

interface RepresentsPreparedParameter
{
	public function getName(): string;

	public function getValue(): string;
}
