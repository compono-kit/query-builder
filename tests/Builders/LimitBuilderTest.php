<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Tests\Builders;

use ComponoKit\Databases\Sql\QueryBuilder\Builders\LimitBuilder;
use PHPUnit\Framework\TestCase;

class LimitBuilderTest extends TestCase
{
	public function testNullLimitReturnsEmptyString(): void
	{
		$this->assertSame('', ( new LimitBuilder(null) )->build());
	}

	public function testFromSqlWithoutOffset(): void
	{
		$this->assertSame(' LIMIT 10', LimitBuilder::fromSql('10')->build());
	}

	public function testFromSqlWithOffset(): void
	{
		$this->assertSame(' LIMIT 5,10', LimitBuilder::fromSql('10', '5')->build());
	}
}
