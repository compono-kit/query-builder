<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Builders;

use ComponoKit\Databases\Sql\QueryBuilder\Exceptions\ValidationException;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Interfaces\RepresentsSubqueryJoinSource;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Interfaces\RepresentsTableName;
use ComponoKit\Databases\Sql\QueryBuilder\Schema\SchemaRegistry;

class QueryValidator
{
	private function __construct()
	{
	}

	public static function validate( SelectBuilder $selectBuilder, QueryBuilder $queryBuilder, SchemaRegistry $schemaRegistry ): void
	{
		$fromTable = $selectBuilder->getFromTable();

		if ( $fromTable === null )
		{
			throw new \LogicException( 'Cannot validate query: from() has not been called.' );
		}

		$aliasMap = self::buildAliasMap( $fromTable, $queryBuilder );

		if ( !$schemaRegistry->hasTable( $fromTable->getName() ) )
		{
			throw new ValidationException( sprintf( 'Table "%s" in FROM clause does not exist in schema.', $fromTable->getName() ) );
		}

		foreach ( $queryBuilder->getJoinClauses() as $joinClause )
		{
			$joinTable = $joinClause->getJoinTable();

			if ( $joinTable instanceof RepresentsSubqueryJoinSource )
			{
				continue;
			}

			if ( !$schemaRegistry->hasTable( $joinTable->getName() ) )
			{
				throw new ValidationException( sprintf( 'Table "%s" in JOIN clause does not exist in schema.', $joinTable->getName() ) );
			}
		}

		foreach ( $selectBuilder->getSelectExpressions() as $expression )
		{
			self::validateSelectExpression( $expression, $aliasMap, $schemaRegistry );
		}
	}

	private static function buildAliasMap( RepresentsTableName $fromTable, QueryBuilder $queryBuilder ): array
	{
		$aliasMap = [];

		$fromName            = strtolower( $fromTable->getName() );
		$aliasMap[$fromName] = $fromName;

		if ( $fromTable->hasAlias() )
		{
			$aliasMap[strtolower( $fromTable->getAlias() )] = $fromName;
		}

		foreach ( $queryBuilder->getJoinClauses() as $joinClause )
		{
			$joinTable = $joinClause->getJoinTable();

			if ( $joinTable instanceof RepresentsSubqueryJoinSource )
			{
				if ( $joinTable->hasAlias() )
				{
					$aliasMap[strtolower( $joinTable->getAlias() )] = null;
				}
				continue;
			}

			$tableName            = strtolower( $joinTable->getName() );
			$aliasMap[$tableName] = $tableName;

			if ( $joinTable->hasAlias() )
			{
				$aliasMap[strtolower( $joinTable->getAlias() )] = $tableName;
			}
		}

		return $aliasMap;
	}

	private static function validateSelectExpression( string $expression, array $aliasMap, SchemaRegistry $schemaRegistry ): void
	{
		$pattern = '/^([a-zA-Z_][a-zA-Z0-9_]*)\.([a-zA-Z_][a-zA-Z0-9_]*)(?:\s+AS\s+\w+)?$/i';

		if ( !preg_match( $pattern, trim( $expression ), $expressionMatches ) )
		{
			return;
		}

		$tableRef   = strtolower( $expressionMatches[1] );
		$columnName = $expressionMatches[2];

		if ( !array_key_exists( $tableRef, $aliasMap ) )
		{
			throw new ValidationException( sprintf( 'Table reference "%s" in SELECT is not in scope.', $expressionMatches[1] ) );
		}

		$realTableName = $aliasMap[$tableRef];

		if ( $realTableName === null )
		{
			return;
		}

		$tableDefinition = $schemaRegistry->getTable( $realTableName );

		if ( !$tableDefinition->hasColumn( $columnName ) )
		{
			throw new ValidationException( sprintf( 'Column "%s" does not exist in table "%s".', $columnName, $realTableName ) );
		}
	}
}
