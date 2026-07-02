<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Schema;

class TableDefinition
{
	private array $columnNamesMap;

	public function __construct( private readonly string $name, private readonly array $columnNames )
	{
		$this->init( $columnNames );
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function getColumnNames(): array
	{
		return $this->columnNames;
	}

	public function hasColumn( string $name ): bool
	{
		return isset( $this->columnNamesMap[ strtolower( $name ) ] );
	}

	private function init( array $columnNames ): void
	{
		foreach ( $columnNames as $columnName )
		{
			$this->columnNamesMap[ strtolower( $columnName ) ] = 1;
		}
	}
}
