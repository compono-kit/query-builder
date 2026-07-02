<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Builders;

use ComponoKit\Databases\Sql\QueryBuilder\Helpers\Quoter;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Interfaces\RepresentsJoinClause;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Interfaces\RepresentsSubqueryJoinSource;

class JoinBuilder
{
	private function __construct()
	{
	}

	/**
	 * @param RepresentsJoinClause[] $joinClauses
	 */
	public static function build( array $joinClauses ): string
	{
		if ( !$joinClauses )
		{
			return '';
		}

		$parts = [];
		foreach ( $joinClauses as $joinClause )
		{
			$parts[] = self::buildSingle( $joinClause );
		}

		return ' ' . implode( ' ', $parts );
	}

	private static function buildSingle( RepresentsJoinClause $joinClause ): string
	{
		$joinTable = $joinClause->getJoinTable();

		if ( $joinTable instanceof RepresentsSubqueryJoinSource )
		{
			$tableSql = $joinTable->toString();
		}
		else
		{
			$tableSql = Quoter::quote( $joinTable->getName() );

			if ( $joinTable->hasAlias() )
			{
				$tableSql .= ' ' . $joinTable->getAlias();
			}
		}

		$sql = $joinClause->getJoinType()->value . ' ' . $tableSql;

		if ( $joinClause->getOnCondition() !== null )
		{
			$sql .= ' ON ' . $joinClause->getOnCondition()->toString();
		}

		return $sql;
	}
}
