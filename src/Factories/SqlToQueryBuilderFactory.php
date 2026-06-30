<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Factories;

use ComponoKit\Databases\Sql\QueryBuilder\Builders\QueryBuilder;
use ComponoKit\Databases\Sql\QueryBuilder\Factories\Parsers\GroupByClauseParser;
use ComponoKit\Databases\Sql\QueryBuilder\Factories\Parsers\JoinClauseParser;
use ComponoKit\Databases\Sql\QueryBuilder\Factories\Parsers\LimitClauseParser;
use ComponoKit\Databases\Sql\QueryBuilder\Factories\Parsers\OrderByClauseParser;
use ComponoKit\Databases\Sql\QueryBuilder\Factories\Parsers\SqlClauseExtractor;
use ComponoKit\Databases\Sql\QueryBuilder\Factories\Parsers\WhereClauseParser;

class SqlToQueryBuilderFactory
{
	private function __construct()
	{
	}

	public static function fromSql( string $sql ): ParsedSqlQuery
	{
		$clauses = SqlClauseExtractor::extract( $sql );
		$builder = new QueryBuilder();

		if ( $clauses['join'] !== null )
		{
			$builder = $builder->addJoinClauses( JoinClauseParser::parse( $clauses['join'] ) );
		}

		if ( $clauses['where'] !== null )
		{
			$builder = $builder->addCriteria( WhereClauseParser::parse( $clauses['where'] ) );
		}

		if ( $clauses['orderBy'] !== null )
		{
			$builder = $builder->addOrderByList( OrderByClauseParser::parse( $clauses['orderBy'] ) );
		}

		if ( $clauses['groupBy'] !== null )
		{
			$builder = $builder->addGroupByList( GroupByClauseParser::parse( $clauses['groupBy'] ) );
		}

		if ( $clauses['having'] !== null )
		{
			$builder = $builder->addHavingCriteria( WhereClauseParser::parse( $clauses['having'] ) );
		}

		if ( $clauses['limit'] !== null )
		{
			$builder = $builder->useLimit( LimitClauseParser::parse( $clauses['limit'], $clauses['offset'] ) );
		}

		return new ParsedSqlQuery( $builder );
	}
}
