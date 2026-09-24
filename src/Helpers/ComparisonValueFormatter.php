<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Helpers;

use ComponoKit\Databases\Sql\QueryBuilder\Models\Interfaces\RepresentsComparisonValue;

class ComparisonValueFormatter
{
	private function __construct()
	{

	}

	public static function format( RepresentsComparisonValue $value ): string
	{
		if ( $value->isColumn() || !is_string( $value->toRawType() ) )
		{
			return (string)$value->toRawType();
		}

		return "'" . $value->toRawType() . "'";
	}
}
