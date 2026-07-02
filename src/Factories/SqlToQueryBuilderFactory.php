<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Factories;

use ComponoKit\Databases\Sql\QueryBuilder\Builders\QueryBuilder;
use ComponoKit\Databases\Sql\QueryBuilder\Builders\SelectBuilder;
use ComponoKit\Databases\Sql\QueryBuilder\Models\ExtractedClauses;
use ComponoKit\Databases\Sql\QueryBuilder\Parsers\GroupByClauseParser;
use ComponoKit\Databases\Sql\QueryBuilder\Parsers\JoinClauseParser;
use ComponoKit\Databases\Sql\QueryBuilder\Parsers\LimitClauseParser;
use ComponoKit\Databases\Sql\QueryBuilder\Parsers\OrderByClauseParser;
use ComponoKit\Databases\Sql\QueryBuilder\Parsers\SelectFromParser;
use ComponoKit\Databases\Sql\QueryBuilder\Parsers\SqlClauseExtractor;
use ComponoKit\Databases\Sql\QueryBuilder\Parsers\WhereClauseParser;

class SqlToQueryBuilderFactory
{
	private ?ExtractedClauses $extractedClauses = null;

	public function __construct( private string $sql )
	{
	}

	public function buildQueryBuilder(): QueryBuilder
	{
		$clauses = $this->getExtractedClauses();
		$builder = new QueryBuilder();

		if ( $clauses->join !== null )
		{
			$builder = $builder->addJoinClauses( JoinClauseParser::parse( $clauses->join ) );
		}

		if ( $clauses->where !== null )
		{
			$builder = $builder->addCriteria( WhereClauseParser::parse( $clauses->where ) );
		}

		if ( $clauses->orderBy !== null )
		{
			$builder = $builder->addOrderByList( OrderByClauseParser::parse( $clauses->orderBy ) );
		}

		if ( $clauses->groupBy !== null )
		{
			$builder = $builder->addGroupByList( GroupByClauseParser::parse( $clauses->groupBy ) );
		}

		if ( $clauses->having !== null )
		{
			$builder = $builder->addHavingCriteria( WhereClauseParser::parse( $clauses->having ) );
		}

		if ( $clauses->limit !== null )
		{
			$builder = $builder->useLimit( LimitClauseParser::parse( $clauses->limit, $clauses->offset ) );
		}

		return $builder;
	}

	public function buildSelectBuilder(): SelectBuilder
	{
		$clauses = $this->getExtractedClauses();

		return SelectFromParser::parse( $clauses->select, $clauses->from );
	}

	private function getExtractedClauses(): ExtractedClauses
	{
		if ( $this->extractedClauses === null )
		{
			$this->extractedClauses = SqlClauseExtractor::extract( $this->sql );
		}

		return $this->extractedClauses;
	}
}
