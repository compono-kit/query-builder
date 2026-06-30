<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Models\Interfaces;

interface RepresentsCriteria extends \JsonSerializable
{
	public function toString(): string;

	/**
	 * @return RepresentsPreparedParameter[]
	 */
	public function getPreparedParameters(): array;
}
