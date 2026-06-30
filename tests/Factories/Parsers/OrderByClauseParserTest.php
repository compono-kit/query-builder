<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Tests\Factories\Parsers;

use ComponoKit\Databases\Sql\QueryBuilder\Factories\Exceptions\SqlParseException;
use ComponoKit\Databases\Sql\QueryBuilder\Factories\Parsers\OrderByClauseParser;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Types\OrderDirection;
use PHPUnit\Framework\TestCase;

class OrderByClauseParserTest extends TestCase
{
	public function testSingleColumnAsc(): void
	{
		$list = OrderByClauseParser::parse('created_at ASC');

		$this->assertCount(1, $list);
		$this->assertSame('created_at', $list[0]->getColumn()->getColumnName()->getPureName());
		$this->assertSame(OrderDirection::ASC, $list[0]->getDirection());
	}

	public function testSingleColumnDesc(): void
	{
		$list = OrderByClauseParser::parse('name DESC');

		$this->assertSame(OrderDirection::DESC, $list[0]->getDirection());
	}

	public function testDefaultDirectionIsAsc(): void
	{
		$list = OrderByClauseParser::parse('id');

		$this->assertSame(OrderDirection::ASC, $list[0]->getDirection());
	}

	public function testTablePrefixedColumn(): void
	{
		$list = OrderByClauseParser::parse('users.created_at DESC');

		$this->assertSame('users', $list[0]->getColumn()->getTableName()->getName());
		$this->assertSame('created_at', $list[0]->getColumn()->getColumnName()->getPureName());
	}

	public function testMultipleColumns(): void
	{
		$list = OrderByClauseParser::parse('name ASC, created_at DESC');

		$this->assertCount(2, $list);
		$this->assertSame('name', $list[0]->getColumn()->getColumnName()->getPureName());
		$this->assertSame('created_at', $list[1]->getColumn()->getColumnName()->getPureName());
		$this->assertSame(OrderDirection::DESC, $list[1]->getDirection());
	}

	public function testBacktickedIdentifiers(): void
	{
		$list = OrderByClauseParser::parse('`users`.`created_at` DESC');

		$this->assertSame('users', $list[0]->getColumn()->getTableName()->getName());
		$this->assertSame('created_at', $list[0]->getColumn()->getColumnName()->getPureName());
	}

	public function testInvalidPartThrows(): void
	{
		$this->expectException(SqlParseException::class);
		OrderByClauseParser::parse('123invalid');
	}
}
