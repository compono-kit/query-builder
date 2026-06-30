<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Models\Types;

enum JoinType: string
{
	case INNER = 'INNER JOIN';
	case LEFT  = 'LEFT JOIN';
	case RIGHT = 'RIGHT JOIN';
	case CROSS = 'CROSS JOIN';
}
