<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Builders;

use ComponoKit\Databases\Sql\QueryBuilder\Builders\Interfaces\BuildsQueries;
use ComponoKit\Databases\Sql\QueryBuilder\Builders\Interfaces\BuildsStatements;
use ComponoKit\Databases\Sql\QueryBuilder\Helpers\Quoter;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Interfaces\RepresentsTableName;
use ComponoKit\Databases\Sql\QueryBuilder\Models\TableName;
use ComponoKit\Databases\Sql\QueryBuilder\Parsers\SelectFromParser;

class SelectBuilder implements BuildsStatements
{
	public function __construct(
		private readonly array $selectExpressions = ['*'],
		private readonly ?RepresentsTableName $fromTable = null
	) {
	}

	public static function fromSql( ?string $selectClause, ?string $fromClause ): self
	{
		return SelectFromParser::parse( $selectClause, $fromClause );
	}

	public function useSelectExpressions( string ...$selectExpressions ): self
	{
		return new self( $selectExpressions, $this->fromTable );
	}

	public function useTable( string $table, ?string $alias = null ): self
	{
		return new self( $this->selectExpressions, new TableName( $table, $alias ) );
	}

	public function getSelectExpressions(): array
	{
		return $this->selectExpressions;
	}

	public function getFromTable(): ?RepresentsTableName
	{
		return $this->fromTable;
	}

	public function build( BuildsQueries $queryBuilder ): string
	{
		return $this->buildSelectFrom()
			. $queryBuilder->buildJoin()
			. $queryBuilder->buildWhereStatement()
			. $queryBuilder->buildGroupBy()
			. $queryBuilder->buildHaving()
			. $queryBuilder->buildOrderBy()
			. $queryBuilder->buildLimit();
	}

	public function getPreparedParams( BuildsQueries $queryBuilder ): array
	{
		return $queryBuilder->getPreparedParams();
	}

	public function buildSelectFrom(): string
	{
		if ( $this->fromTable === null )
		{
			throw new \LogicException( 'Cannot build query: useTable() has not been called.' );
		}

		return $this->buildSelect() . ' ' . $this->buildFrom();
	}

	private function buildSelect(): string
	{
		if ( !$this->selectExpressions )
		{
			return 'SELECT *';
		}

		return 'SELECT ' . implode( ', ', $this->selectExpressions );
	}

	private function buildFrom(): string
	{
		$sql = 'FROM ' . Quoter::quote( $this->fromTable->getName() );

		if ( $this->fromTable->hasAlias() )
		{
			$sql .= ' ' . $this->fromTable->getAlias();
		}

		return $sql;
	}
}
