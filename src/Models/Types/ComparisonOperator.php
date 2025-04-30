<?php declare(strict_types=1);

namespace ComponoKit\QueryBuilder\Models\Types;

class ComparisonOperator extends AbstractString
{
	public const EQUAL_OPERATOR            = '=';

	public const NOT_EQUAL_OPERATOR        = '!=';

	public const GREATER_OPERATOR          = '>';

	public const LESS_OPERATOR             = '<';

	public const EQUAL_OR_GREATER_OPERATOR = '>=';

	public const EQUAL_OR_LESS_OPERATOR    = '<=';

	public const LIKE_OPERATOR             = 'LIKE';

	public const NOT_LIKE_OPERATOR         = 'NOT LIKE';

	public const IN_OPERATOR               = 'IN';

	public const NOT_IN_OPERATOR           = 'NOT IN';

	public const IS_NULL_OPERATOR          = 'IS NULL';

	public const IS_NOT_NULL_OPERATOR      = 'IS NOT NULL';

	private static array $validValues = [
		self::EQUAL_OPERATOR            => 1,
		self::NOT_EQUAL_OPERATOR        => 1,
		self::GREATER_OPERATOR          => 1,
		self::LESS_OPERATOR             => 1,
		self::EQUAL_OR_GREATER_OPERATOR => 1,
		self::EQUAL_OR_LESS_OPERATOR    => 1,
		self::LIKE_OPERATOR             => 1,
		self::NOT_LIKE_OPERATOR         => 1,
		self::IN_OPERATOR               => 1,
		self::NOT_IN_OPERATOR           => 1,
		self::IS_NULL_OPERATOR          => 1,
		self::IS_NOT_NULL_OPERATOR      => 1,
	];

	public static function isValid( string $value ): bool
	{
		return isset( self::$validValues[ $value ] );
	}

	public static function equalOperator(): self
	{
		return new self( self::EQUAL_OPERATOR );
	}

	public static function notEqualOperator(): self
	{
		return new self( self::NOT_EQUAL_OPERATOR );
	}

	public static function greaterOperator(): self
	{
		return new self( self::GREATER_OPERATOR );
	}

	public static function lessOperator(): self
	{
		return new self( self::LESS_OPERATOR );
	}

	public static function equalOrGreaterOperator(): self
	{
		return new self( self::EQUAL_OR_GREATER_OPERATOR );
	}

	public static function equalOrLessOperator(): self
	{
		return new self( self::EQUAL_OR_LESS_OPERATOR );
	}

	public static function likeOperator(): self
	{
		return new self( self::LIKE_OPERATOR );
	}

	public static function notLikeOperator(): self
	{
		return new self( self::NOT_LIKE_OPERATOR );
	}

	public static function inOperator(): self
	{
		return new self( self::IN_OPERATOR );
	}

	public static function notInOperator(): self
	{
		return new self( self::NOT_IN_OPERATOR );
	}

	public static function isNullOperator(): self
	{
		return new self( self::IS_NULL_OPERATOR );
	}

	public static function isNotNullOperator(): self
	{
		return new self( self::IS_NOT_NULL_OPERATOR );
	}

	public function isEqualOperator(): bool
	{
		return $this->toString() === self::EQUAL_OPERATOR;
	}

	public function isNotEqualOperator(): bool
	{
		return $this->toString() === self::NOT_EQUAL_OPERATOR;
	}

	public function isGreaterOperator(): bool
	{
		return $this->toString() === self::GREATER_OPERATOR;
	}

	public function isLessOperator(): bool
	{
		return $this->toString() === self::LESS_OPERATOR;
	}

	public function isEqualOrGreaterOperator(): bool
	{
		return $this->toString() === self::EQUAL_OR_GREATER_OPERATOR;
	}

	public function isEqualOrLessOperator(): bool
	{
		return $this->toString() === self::EQUAL_OR_LESS_OPERATOR;
	}

	public function isLikeOperator(): bool
	{
		return $this->toString() === self::LIKE_OPERATOR;
	}

	public function isNotLikeOperator(): bool
	{
		return $this->toString() === self::NOT_LIKE_OPERATOR;
	}

	public function isInOperator(): bool
	{
		return $this->toString() === self::IN_OPERATOR;
	}

	public function isNotInOperator(): bool
	{
		return $this->toString() === self::NOT_IN_OPERATOR;
	}

	public function isIsNullOperator(): bool
	{
		return $this->toString() === self::IS_NULL_OPERATOR;
	}

	public function isIsNotNullOperator(): bool
	{
		return $this->toString() === self::IS_NOT_NULL_OPERATOR;
	}
}
