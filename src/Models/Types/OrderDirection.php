<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Models\Types;

enum OrderDirection: string
{
	case ASC = 'ASC';

	case DESC = 'DESC';
}
