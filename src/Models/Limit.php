<?php declare(strict_types=1);

namespace ComponoKit\QueryBuilder\Models;

use ComponoKit\QueryBuilder\Models\Interfaces\RepresentsLimit;

class Limit implements RepresentsLimit
{
	public function __construct( private readonly int $count, private readonly int $offset = 0 )
	{
	}

	public function getOffset(): int
	{
		return $this->offset;
	}

	public function getCount(): int
	{
		return $this->count;
	}

}
