<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Models\Interfaces;

interface RepresentsSubqueryJoinSource extends RepresentsTableName
{
	public function getSubquerySql(): string;
}
