<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Models\Types;

enum LogicalOperator
{
	case AND;

	case OR;
}
