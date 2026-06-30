<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Builders;

use ComponoKit\Databases\Sql\QueryBuilder\Models\Limit;

class LimitBuilder
{
	private function __construct()
	{
	}

	public static function build( ?Limit $limit ): string
	{
		if ( null === $limit || $limit->getCount() <= 0 )
		{
			return '';
		}

		if ( $limit->getOffset() > 0 )
		{
			return sprintf( ' LIMIT %d,%d', $limit->getOffset(), $limit->getCount() );
		}

		return ' LIMIT ' . $limit->getCount();
	}
}
