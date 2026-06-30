<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Tests\Factories\Parsers;

use ComponoKit\Databases\Sql\QueryBuilder\Criterias\Conditions\Condition;
use ComponoKit\Databases\Sql\QueryBuilder\Criterias\Conditions\IsNotNullCondition;
use ComponoKit\Databases\Sql\QueryBuilder\Criterias\Conditions\IsNullCondition;
use ComponoKit\Databases\Sql\QueryBuilder\Criterias\Criteria;
use ComponoKit\Databases\Sql\QueryBuilder\Criterias\IsolatedCriteria;
use ComponoKit\Databases\Sql\QueryBuilder\Factories\Parsers\WhereClauseParser;
use PHPUnit\Framework\TestCase;

class WhereClauseParserTest extends TestCase
{
	public function testSimpleEquality(): void
	{
		$criteria = WhereClauseParser::parse("id = 1");

		$this->assertInstanceOf(Condition::class, $criteria);
		$this->assertSame("`id` = 1", $criteria->toString());
	}

	public function testStringValue(): void
	{
		$criteria = WhereClauseParser::parse("status = 'active'");

		$this->assertSame("`status` = 'active'", $criteria->toString());
	}

	public function testPreparedParameter(): void
	{
		$criteria = WhereClauseParser::parse("id = :userId");

		$this->assertSame("`id` = :userId", $criteria->toString());
	}

	public function testTablePrefixedColumn(): void
	{
		$criteria = WhereClauseParser::parse("users.id = 1");

		$this->assertSame("`users`.`id` = 1", $criteria->toString());
	}

	public function testIsNull(): void
	{
		$criteria = WhereClauseParser::parse("deleted_at IS NULL");

		$this->assertInstanceOf(IsNullCondition::class, $criteria);
		$this->assertSame("`deleted_at` IS NULL", $criteria->toString());
	}

	public function testIsNotNull(): void
	{
		$criteria = WhereClauseParser::parse("deleted_at IS NOT NULL");

		$this->assertInstanceOf(IsNotNullCondition::class, $criteria);
		$this->assertSame("`deleted_at` IS NOT NULL", $criteria->toString());
	}

	public function testAndExpression(): void
	{
		$criteria = WhereClauseParser::parse("id = 1 AND status = 'active'");

		$this->assertInstanceOf(Criteria::class, $criteria);
		$this->assertStringContainsString('AND', $criteria->toString());
	}

	public function testOrExpression(): void
	{
		$criteria = WhereClauseParser::parse("status = 'active' OR status = 'pending'");

		$this->assertInstanceOf(Criteria::class, $criteria);
		$this->assertStringContainsString('OR', $criteria->toString());
	}

	public function testParenthesizedGroup(): void
	{
		$criteria = WhereClauseParser::parse("(a = 1 OR b = 2)");

		$this->assertInstanceOf(IsolatedCriteria::class, $criteria);
		$this->assertStringContainsString('(', $criteria->toString());
	}

	public function testNestedGroup(): void
	{
		$criteria = WhereClauseParser::parse("id = 1 AND (status = 'active' OR status = 'pending')");

		$this->assertInstanceOf(Criteria::class, $criteria);
		$output = $criteria->toString();
		$this->assertStringContainsString('AND', $output);
		$this->assertStringContainsString('(', $output);
		$this->assertStringContainsString('OR', $output);
	}

	public function testInValues(): void
	{
		$criteria = WhereClauseParser::parse("id IN (1, 2, 3)");

		$this->assertStringContainsString('IN', $criteria->toString());
	}

	public function testNotInValues(): void
	{
		$criteria = WhereClauseParser::parse("id NOT IN (1, 2, 3)");

		$this->assertStringContainsString('NOT IN', $criteria->toString());
	}

	public function testLike(): void
	{
		$criteria = WhereClauseParser::parse("name LIKE '%foo%'");

		$this->assertSame("`name` LIKE '%foo%'", $criteria->toString());
	}

	public function testNotLike(): void
	{
		$criteria = WhereClauseParser::parse("name NOT LIKE '%foo%'");

		$this->assertSame("`name` NOT LIKE '%foo%'", $criteria->toString());
	}

	public function testGreaterThan(): void
	{
		$criteria = WhereClauseParser::parse("age > 18");

		$this->assertSame("`age` > 18", $criteria->toString());
	}

	public function testOrWithAndGroupsAreWrapped(): void
	{
		$criteria = WhereClauseParser::parse("a = 1 OR b = 2 AND c = 3");

		// b = 2 AND c = 3 should be wrapped in parens as IsolatedCriteria
		$output = $criteria->toString();
		$this->assertStringContainsString('OR', $output);
		$this->assertStringContainsString('(', $output);
	}

	public function testColumnToColumnComparison(): void
	{
		$criteria = WhereClauseParser::parse("a.id = b.id");

		$this->assertStringContainsString('`a`.`id`', $criteria->toString());
		$this->assertStringContainsString('`b`.`id`', $criteria->toString());
	}

	public function testFloatValue(): void
	{
		$criteria = WhereClauseParser::parse("price > 9.99");

		$this->assertSame("`price` > 9.99", $criteria->toString());
	}
}
