<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Models;

use ComponoKit\Databases\Sql\QueryBuilder\Models\Interfaces\RepresentsCriteria;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Interfaces\RepresentsJoinClause;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Interfaces\RepresentsTableName;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Types\JoinType;

class JoinClause implements RepresentsJoinClause
{
	public function __construct(
		private readonly RepresentsTableName $joinTable,
		private readonly JoinType $joinType,
		private readonly ?RepresentsCriteria $onCondition = null
	) {
	}

	public function getJoinTable(): RepresentsTableName
	{
		return $this->joinTable;
	}

	public function getJoinType(): JoinType
	{
		return $this->joinType;
	}

	public function getOnCondition(): ?RepresentsCriteria
	{
		return $this->onCondition;
	}
}
