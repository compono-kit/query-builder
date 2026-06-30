<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Models;

use ComponoKit\Databases\Sql\QueryBuilder\Models\Interfaces\RepresentsPreparedParameter;

class PreparedParameter implements RepresentsPreparedParameter
{
	public function __construct( private readonly string $name, private readonly string $value )
	{
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function getValue(): string
	{
		return $this->value;
	}
}
