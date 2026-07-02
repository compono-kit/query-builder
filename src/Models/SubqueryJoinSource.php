<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Models;

use ComponoKit\Databases\Sql\QueryBuilder\Models\Interfaces\RepresentsSubqueryJoinSource;

class SubqueryJoinSource implements RepresentsSubqueryJoinSource
{
	public function __construct(
		private readonly string $subquerySql,
		private readonly ?string $alias = null
	) {
	}

	public function getSubquerySql(): string
	{
		return $this->subquerySql;
	}

	public function getName(): string
	{
		return $this->subquerySql;
	}

	public function getAlias(): ?string
	{
		return $this->alias;
	}

	public function hasAlias(): bool
	{
		return $this->alias !== null;
	}

	public function toString(): string
	{
		return sprintf(
			'(%s)%s',
			$this->subquerySql,
			$this->alias !== null ? ' ' . $this->alias : ''
		);
	}

	public function __toString(): string
	{
		return $this->toString();
	}

	public function jsonSerialize(): string
	{
		return $this->toString();
	}
}
