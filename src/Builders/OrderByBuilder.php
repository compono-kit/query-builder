<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Builders;

use ComponoKit\Databases\Sql\QueryBuilder\Models\Interfaces\RepresentsOrderBy;
use ComponoKit\Databases\Sql\QueryBuilder\Parsers\OrderByClauseParser;

class OrderByBuilder
{
	/**
	 * @param RepresentsOrderBy[] $orderByList
	 */
	public function __construct( private readonly array $orderByList )
	{
	}

	public static function fromSql( string $orderByClause ): self
	{
		return new self( OrderByClauseParser::parse( $orderByClause ) );
	}

	public function build(): string
	{
		if ( $this->orderByList )
		{
			$columnDirections = [];
			foreach ( $this->orderByList as $orderBy )
			{
				$columnDirections[] = sprintf( '%s %s', $orderBy->getColumn()->toString(), $orderBy->getDirection()->name );
			}

			return ' ORDER BY ' . implode( ', ', $columnDirections );
		}

		return '';
	}
}
