<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Builders;

use ComponoKit\Databases\Sql\QueryBuilder\Models\Interfaces\RepresentsCriteria;
use ComponoKit\Databases\Sql\QueryBuilder\Parsers\WhereClauseParser;

class HavingBuilder
{
	/**
	 * @param RepresentsCriteria[] $criterias
	 */
	public function __construct( private readonly array $criterias )
	{
	}

	public static function fromSql( string $havingClause ): self
	{
		return new self( [ WhereClauseParser::parse( $havingClause ) ] );
	}

	public function build(): string
	{
		$statements = [];
		foreach ( $this->criterias as $criteria )
		{
			$statements[] = $criteria->toString();
		}

		if ( $statements )
		{
			return sprintf( ' HAVING %s', implode( ' AND ', $statements ) );
		}

		return '';
	}
}
