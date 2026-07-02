<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Models;

use ComponoKit\Databases\Sql\QueryBuilder\Models\Types\TokenType;

class Token
{
	public function __construct(
		public readonly TokenType $type,
		public readonly string    $rawMatch,
		public readonly string    $value = '',
		public readonly ?string   $tablePrefix = null
	) {
	}
}
