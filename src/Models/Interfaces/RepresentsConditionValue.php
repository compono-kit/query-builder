<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Models\Interfaces;

interface RepresentsConditionValue
{
	public function getColumn(): RepresentsColumn;

	public function getPreparedParameter(): ?RepresentsPreparedParameter;

	public function getValue(): ?RepresentsComparisonValue;
}
