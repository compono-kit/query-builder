<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Builders;

use ComponoKit\Databases\Sql\QueryBuilder\Models\Interfaces\RepresentsColumn;
use ComponoKit\Databases\Sql\QueryBuilder\Parsers\GroupByClauseParser;

class GroupByBuilder
{
	/**
	 * @param RepresentsColumn[] $columns
	 */
	public function __construct( private readonly array $columns )
	{
	}

	public static function fromSql( string $groupByClause ): self
	{
		return new self( GroupByClauseParser::parse( $groupByClause ) );
	}

	public function build(): string
	{
		if ( $this->columns )
		{
			$columnStrings = [];
			foreach ( $this->columns as $column )
			{
				$columnStrings[] = $column->toString();
			}

			return ' GROUP BY ' . implode( ', ', $columnStrings );
		}

		return '';
	}
}
