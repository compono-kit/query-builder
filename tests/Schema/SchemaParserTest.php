<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Tests\Schema;

use ComponoKit\Databases\Sql\QueryBuilder\Schema\SchemaParser;
use PHPUnit\Framework\TestCase;

class SchemaParserTest extends TestCase
{
	public function testParsesUnquotedTableAndColumnNames(): void
	{
		$registry = SchemaParser::fromSql( 'CREATE TABLE users (id INT, name VARCHAR(255), email VARCHAR(255));' );

		$this->assertTrue( $registry->hasTable( 'users' ) );
		$table = $registry->getTable( 'users' );
		$this->assertTrue( $table->hasColumn( 'id' ) );
		$this->assertTrue( $table->hasColumn( 'name' ) );
		$this->assertTrue( $table->hasColumn( 'email' ) );
	}

	public function testParsesBacktickQuotedNames(): void
	{
		$registry = SchemaParser::fromSql( 'CREATE TABLE `delivery-orders` (`id` INT, `order_id` INT);' );

		$this->assertTrue( $registry->hasTable( 'delivery-orders' ) );
		$table = $registry->getTable( 'delivery-orders' );
		$this->assertTrue( $table->hasColumn( 'id' ) );
		$this->assertTrue( $table->hasColumn( 'order_id' ) );
	}

	public function testIgnoresConstraintLines(): void
	{
		$sql = '
			CREATE TABLE orders (
				id INT NOT NULL AUTO_INCREMENT,
				user_id INT NOT NULL,
				total DECIMAL(10,2),
				PRIMARY KEY (id),
				INDEX idx_user (user_id),
				FOREIGN KEY (user_id) REFERENCES users (id),
				UNIQUE KEY unique_total (total),
				CONSTRAINT chk_total CHECK (total >= 0)
			);
		';

		$registry = SchemaParser::fromSql( $sql );
		$table    = $registry->getTable( 'orders' );

		$this->assertTrue( $table->hasColumn( 'id' ) );
		$this->assertTrue( $table->hasColumn( 'user_id' ) );
		$this->assertTrue( $table->hasColumn( 'total' ) );
		$this->assertFalse( $table->hasColumn( 'PRIMARY' ) );
		$this->assertFalse( $table->hasColumn( 'INDEX' ) );
		$this->assertFalse( $table->hasColumn( 'FOREIGN' ) );
	}

	public function testHandlesMultipleTables(): void
	{
		$sql = '
			CREATE TABLE users (id INT, name VARCHAR(255));
			CREATE TABLE orders (id INT, user_id INT, total DECIMAL(10,2));
		';

		$registry = SchemaParser::fromSql( $sql );

		$this->assertTrue( $registry->hasTable( 'users' ) );
		$this->assertTrue( $registry->hasTable( 'orders' ) );
		$this->assertFalse( $registry->hasTable( 'products' ) );
	}

	public function testTableLookupIsCaseInsensitive(): void
	{
		$registry = SchemaParser::fromSql( 'CREATE TABLE Users (Id INT, Name VARCHAR(255));' );

		$this->assertTrue( $registry->hasTable( 'users' ) );
		$this->assertTrue( $registry->hasTable( 'USERS' ) );
		$table = $registry->getTable( 'USERS' );
		$this->assertTrue( $table->hasColumn( 'ID' ) );
		$this->assertTrue( $table->hasColumn( 'name' ) );
	}

	public function testFromFileReadsFixture(): void
	{
		$registry = SchemaParser::fromFile( __DIR__ . '/../fixtures/schema.sql' );

		$this->assertTrue( $registry->hasTable( 'users' ) );
		$this->assertTrue( $registry->hasTable( 'orders' ) );
		$this->assertTrue( $registry->hasTable( 'delivery-orders' ) );
		$this->assertTrue( $registry->getTable( 'users' )->hasColumn( 'email' ) );
		$this->assertTrue( $registry->getTable( 'orders' )->hasColumn( 'user_id' ) );
		$this->assertTrue( $registry->getTable( 'delivery-orders' )->hasColumn( 'address' ) );
	}

	public function testEmptySqlReturnsEmptyRegistry(): void
	{
		$registry = SchemaParser::fromSql( '' );

		$this->assertFalse( $registry->hasTable( 'anything' ) );
	}
}
