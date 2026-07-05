<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Tests\Builders;

use ComponoKit\Databases\Sql\QueryBuilder\Builders\OrderByBuilder;
use PHPUnit\Framework\TestCase;

class OrderByBuilderTest extends TestCase
{
	public function testEmptyListReturnsEmptyString(): void
	{
		$this->assertSame('', ( new OrderByBuilder([]) )->build());
	}

	public function testFromSqlParsesClause(): void
	{
		$output = OrderByBuilder::fromSql('created_at DESC')->build();

		$this->assertStringContainsString('ORDER BY', $output);
		$this->assertStringContainsString('created_at', $output);
		$this->assertStringContainsString('DESC', $output);
	}
}
