<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Validators;

use ComponoKit\Databases\Sql\QueryBuilder\Exceptions\ValidationException;

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
