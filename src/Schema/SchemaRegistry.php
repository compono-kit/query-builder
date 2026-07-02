<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Schema;

use ComponoKit\Databases\Sql\QueryBuilder\Exceptions\ValidationException;

class SchemaRegistry
{
	private readonly array $tables;

	private function __construct( array $tables )
	{
		$this->tables = $tables;
	}

	/**
	 * @param TableDefinition[] $definitions
	 */
	public static function fromDefinitions( array $definitions ): self
	{
		$tables = [];

		foreach ( $definitions as $definition )
		{
			$tables[strtolower( $definition->getName() )] = $definition;
		}

		return new self( $tables );
	}

	public function hasTable( string $name ): bool
	{
		return isset( $this->tables[strtolower( $name )] );
	}

	public function getTable( string $name ): TableDefinition
	{
		$key = strtolower( $name );

		if ( !isset( $this->tables[$key] ) )
		{
			throw new ValidationException( sprintf( 'Table "%s" does not exist in schema.', $name ) );
		}

		return $this->tables[$key];
	}
}
