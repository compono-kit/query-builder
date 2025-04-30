<?php declare(strict_types=1);

namespace ComponoKit\QueryBuilder\Models\Types;

enum OrderDirection: string
{
	case ASC = 'ASC';

	case DESC = 'DESC';
}
