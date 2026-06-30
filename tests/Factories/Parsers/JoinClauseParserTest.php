<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Tests\Factories\Parsers;

use ComponoKit\Databases\Sql\QueryBuilder\Factories\Exceptions\SqlParseException;
use ComponoKit\Databases\Sql\QueryBuilder\Factories\Parsers\JoinClauseParser;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Types\JoinType;
use PHPUnit\Framework\TestCase;

class JoinClauseParserTest extends TestCase
{
	public function testSimpleInnerJoin(): void
	{
		$joinClauses = JoinClauseParser::parse('INNER JOIN orders o ON u.id = o.user_id');

		$this->assertCount(1, $joinClauses);
		$this->assertSame(JoinType::INNER, $joinClauses[0]->getJoinType());
		$this->assertSame('orders', $joinClauses[0]->getJoinTable()->getName());
		$this->assertSame('o', $joinClauses[0]->getJoinTable()->getAlias());
		$this->assertNotNull($joinClauses[0]->getOnCondition());
	}

	public function testLeftJoin(): void
	{
		$joinClauses = JoinClauseParser::parse('LEFT JOIN products p ON o.product_id = p.id');

		$this->assertCount(1, $joinClauses);
		$this->assertSame(JoinType::LEFT, $joinClauses[0]->getJoinType());
	}

	public function testRightJoin(): void
	{
		$joinClauses = JoinClauseParser::parse('RIGHT JOIN categories c ON p.category_id = c.id');

		$this->assertCount(1, $joinClauses);
		$this->assertSame(JoinType::RIGHT, $joinClauses[0]->getJoinType());
	}

	public function testCrossJoin(): void
	{
		$joinClauses = JoinClauseParser::parse('CROSS JOIN sizes');

		$this->assertCount(1, $joinClauses);
		$this->assertSame(JoinType::CROSS, $joinClauses[0]->getJoinType());
		$this->assertNull($joinClauses[0]->getOnCondition());
	}

	public function testBareJoinDefaultsToInner(): void
	{
		$joinClauses = JoinClauseParser::parse('JOIN orders o ON u.id = o.user_id');

		$this->assertCount(1, $joinClauses);
		$this->assertSame(JoinType::INNER, $joinClauses[0]->getJoinType());
	}

	public function testLeftOuterJoinNormalizesToLeft(): void
	{
		$joinClauses = JoinClauseParser::parse('LEFT OUTER JOIN orders o ON u.id = o.user_id');

		$this->assertSame(JoinType::LEFT, $joinClauses[0]->getJoinType());
	}

	public function testMultipleJoins(): void
	{
		$joinClauses = JoinClauseParser::parse('INNER JOIN orders o ON u.id = o.user_id LEFT JOIN products p ON o.product_id = p.id');

		$this->assertCount(2, $joinClauses);
		$this->assertSame(JoinType::INNER, $joinClauses[0]->getJoinType());
		$this->assertSame(JoinType::LEFT, $joinClauses[1]->getJoinType());
	}

	public function testJoinWithAliasKeyword(): void
	{
		$joinClauses = JoinClauseParser::parse('INNER JOIN orders AS o ON u.id = o.user_id');

		$this->assertSame('orders', $joinClauses[0]->getJoinTable()->getName());
		$this->assertSame('o', $joinClauses[0]->getJoinTable()->getAlias());
	}

	public function testJoinTableWithoutAlias(): void
	{
		$joinClauses = JoinClauseParser::parse('INNER JOIN orders ON u.id = orders.user_id');

		$this->assertSame('orders', $joinClauses[0]->getJoinTable()->getName());
		$this->assertNull($joinClauses[0]->getJoinTable()->getAlias());
	}

	public function testOnConditionWithAnd(): void
	{
		$joinClauses = JoinClauseParser::parse("INNER JOIN orders o ON u.id = o.user_id AND o.status = 'active'");

		$this->assertNotNull($joinClauses[0]->getOnCondition());
		$this->assertStringContainsString('AND', $joinClauses[0]->getOnCondition()->toString());
	}

	public function testOnConditionUsesBacktickQuoting(): void
	{
		$joinClauses = JoinClauseParser::parse('INNER JOIN orders o ON u.id = o.user_id');

		$this->assertStringContainsString('`id`', $joinClauses[0]->getOnCondition()->toString());
	}

	public function testInvalidSegmentThrows(): void
	{
		$this->expectException(SqlParseException::class);

		JoinClauseParser::parse('JOIN 123invalid ON ...');
	}
}
