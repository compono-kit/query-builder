<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Models\Types;

use ComponoKit\Databases\Sql\QueryBuilder\Exceptions\ValidationException;

abstract class AbstractString implements \Stringable, \JsonSerializable
{
	private string $value;

	public function __construct( string $value )
	{
		$this->validate( $value );
		$this->value = $this->transform( $value );
	}

	abstract public static function isValid( string $value ): bool;

	protected function transform( string $value ): string
	{
		return $value;
	}

	public function jsonSerialize(): string
	{
		return $this->value;
	}

	public function toString(): string
	{
		return $this->value;
	}

	public function __toString(): string
	{
		return $this->value;
	}

	protected function validate( string $value ): void
	{
		if ( !static::isValid( $value ) )
		{
			throw new ValidationException(
				sprintf(
					'Invalid %s: %s',
					(new \ReflectionClass( static::class ))->getShortName(),
					$value
				)
			);
		}
	}
}
