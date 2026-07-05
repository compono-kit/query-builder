<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Builders;

use ComponoKit\Databases\Sql\QueryBuilder\Models\Limit;
use ComponoKit\Databases\Sql\QueryBuilder\Parsers\LimitClauseParser;

class LimitBuilder
{
	public function __construct( private readonly ?Limit $limit )
	{
	}

	public static function fromSql( string $limitClause, ?string $offsetClause = null ): self
	{
		return new self( LimitClauseParser::parse( $limitClause, $offsetClause ) );
	}

	public function build(): string
	{
		if ( null === $this->limit || $this->limit->getCount() <= 0 )
		{
			return '';
		}

		if ( $this->limit->getOffset() > 0 )
		{
			return sprintf( ' LIMIT %d,%d', $this->limit->getOffset(), $this->limit->getCount() );
		}

		return ' LIMIT ' . $this->limit->getCount();
	}
}
