<?php
/**
 * Test Script
 */
class mainTest extends PHPUnit_Framework_TestCase{

	private $exdb;

	/**
	 * setup
	 */
	public function setup(){
		mb_internal_encoding('utf-8');
		@date_default_timezone_set('Asia/Tokyo');

		// @unlink(__DIR__.'/testdata/_tmp/db/test.sqlite');
		// clearstatcache();

		$pdo = new \PDO(
			'sqlite:'.__DIR__.'/testdata/_tmp/db/test.sqlite',
			null, null,
			array(
				\PDO::ATTR_PERSISTENT => false, // ←これをtrueにすると、"持続的な接続" になる
			)
		);

		$this->exdb = new excellent_db\create( $pdo, array(
			"prefix" => "excellent_test",
			"path_definition_file" => __DIR__.'/testdata/db/sample_db_tables.xlsx',
			"path_cache_dir" => __DIR__.'/testdata/_tmp/caches/',
		) );

	}

	/**
	 * migrate test
	 */
	public function testMigrateInitTable(){

		$this->assertTrue( is_object($this->exdb) );
		$this->assertTrue( $this->exdb->clearcache() );
		$this->assertTrue( $this->exdb->reload_definition_data() );

		$this->exdb->migrate_init_tables();

	}//testMigrateInitTable()

	/**
	 * INSERT
	 */
	public function testInsert(){

		$result_insert = $this->exdb->insert('user', array(
			'user_account'=>'tester001',
			'password'=>'password',
			'user_name'=>'Tester No.001',
		));
		// var_dump($result_insert);
		$this->assertTrue( $result_insert );

		$result_insert = $this->exdb->insert('user', array(
			'user_account'=>'tester002',
			'password'=>'password',
			'user_name'=>'Tester No.002',
		));
		// var_dump($result_insert);
		$this->assertTrue( $result_insert );

		$result_insert = $this->exdb->insert('user', array(
			'user_account'=>'tester003',
			'password'=>'password',
			'user_name'=>'Tester No.003',
		));
		// var_dump($result_insert);
		$this->assertTrue( $result_insert );

	}//testInsert()

}
