<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Parsers;

class ParenthesisDepth
{
	private function __construct()
	{
	}

	/**
	 * Returns an array mapping each byte position in $sql to its parenthesis
	 * depth at that position (depth before the character is processed).
	 * Single-quoted string literals are skipped so their content does not
	 * affect the depth count.
	 *
	 * @return int[]
	 */
	public static function compute( string $sql ): array
	{
		$depths   = [];
		$depth    = 0;
		$inString = false;
		$length   = strlen( $sql );
		$i        = 0;

		while ( $i < $length )
		{
			$depths[$i] = $depth;

			if ( $inString )
			{
				if ( $sql[$i] === "'" && isset( $sql[$i + 1] ) && $sql[$i + 1] === "'" )
				{
					$depths[$i + 1] = $depth;
					$i              += 2;
					continue;
				}

				if ( $sql[$i] === "'" )
				{
					$inString = false;
				}
			}
			else
			{
				if ( $sql[$i] === "'" )
				{
					$inString = true;
				}
				elseif ( $sql[$i] === '(' )
				{
					$depth++;
				}
				elseif ( $sql[$i] === ')' )
				{
					$depth--;
				}
			}

			$i++;
		}

		return $depths;
	}
}
