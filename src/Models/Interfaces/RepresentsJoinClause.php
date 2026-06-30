<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Models\Interfaces;

use ComponoKit\Databases\Sql\QueryBuilder\Models\Types\JoinType;

interface RepresentsJoinClause
{
	public function getJoinTable(): RepresentsTableName;

	public function getJoinType(): JoinType;

	public function getOnCondition(): ?RepresentsCriteria;
}
