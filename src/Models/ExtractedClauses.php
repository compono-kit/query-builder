<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Models;

class ExtractedClauses
{
	public function __construct(
		public readonly ?string $select = null,
		public readonly ?string $from = null,
		public readonly ?string $join = null,
		public readonly ?string $where = null,
		public readonly ?string $groupBy = null,
		public readonly ?string $having = null,
		public readonly ?string $orderBy = null,
		public readonly ?string $limit = null,
		public readonly ?string $offset = null
	) {
	}
}
