<?php
/**
 * Test Script
 */
class mainTest extends PHPUnit_Framework_TestCase{

	private $pdo;
	private $exdb;

	/**
	 * setup
	 */
	public function setup(){
		mb_internal_encoding('utf-8');
		@date_default_timezone_set('Asia/Tokyo');

		$this->pdo = new \PDO(
			'sqlite:'.__DIR__.'/testdata/_tmp/db/test.sqlite',
			null, null,
			array(
				\PDO::ATTR_PERSISTENT => false, // ←これをtrueにすると、"持続的な接続" になる
			)
		);

		$this->exdb = new excellent_db\create( $this->pdo, array(
			"prefix" => "excellent_test",
			"path_definition_file" => __DIR__.'/testdata/db/sample_db_tables.xlsx',
			"path_cache_dir" => __DIR__.'/testdata/_tmp/caches/',
		) );

	}

	/**
	 * Test
	 */
	public function testMain(){
		@unlink(__DIR__.'/testdata/_tmp/db/test.sqlite');

		$this->assertTrue( is_object($this->exdb) );
		$this->assertTrue( $this->exdb->clearcache() );
		$this->assertTrue( $this->exdb->reload_definition_data() );

		$this->exdb->migrate_init_tables();

	}//testMain()

}
