<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Builders;

use ComponoKit\Databases\Sql\QueryBuilder\Helpers\Quoter;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Interfaces\RepresentsTableName;
use ComponoKit\Databases\Sql\QueryBuilder\Models\TableName;

class SelectBuilder
{
	private function __construct(
		private readonly array $selectExpressions,
		private readonly ?RepresentsTableName $fromTable
	) {
	}

	public static function create(): self
	{
		return new self( ['*'], null );
	}

	public function select( array $expressions ): self
	{
		return new self( $expressions, $this->fromTable );
	}

	public function from( string $table, ?string $alias = null ): self
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

	public function build(): string
	{
		if ( $this->fromTable === null )
		{
			throw new \LogicException( 'Cannot build query: from() has not been called.' );
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
