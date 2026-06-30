<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Factories\Parsers;

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
