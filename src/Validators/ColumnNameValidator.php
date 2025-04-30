<?php declare(strict_types=1);

namespace ComponoKit\QueryBuilder\Validators;

use ComponoKit\QueryBuilder\Exceptions\ValidationException;

class ColumnNameValidator
{
	private function __construct()
	{
	}

	public static function validate( string $value ): void
	{
		if ( !self::isValid( $value ) )
		{
			throw new ValidationException( 'Invalid column name: ' . $value );
		}
	}

	public static function isValid( string $value ): bool
	{
		return $value !== '';
	}
}
