<?php declare(strict_types=1);

namespace ComponoKit\QueryBuilder\Models\Interfaces;

interface RepresentsCriteria extends \JsonSerializable
{
	public function toString(): string;

	/**
	 * @return RepresentsPreparedParameter[]
	 */
	public function getPreparedParameters(): array;
}
