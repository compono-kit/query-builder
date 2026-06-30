<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Models;

class TableName extends AbstractTableName
{
	public function __construct( private readonly string $name, private readonly ?string $alias = null )
	{
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function getAlias(): ?string
	{
		return $this->alias;
	}
}
