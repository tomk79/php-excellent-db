<?php
/**
 * Test Script
 */
class mainTest extends PHPUnit_Framework_TestCase{

	/**
	 * setup
	 */
	public function setup(){
		mb_internal_encoding('utf-8');
		@date_default_timezone_set('Asia/Tokyo');
	}

	/**
	 * Test
	 */
	public function testMain(){
		$exdb = new excellent_db\create(
			// DB Config.
			array(
				"dbms" => "sqlite",
				"host" => __DIR__.'/testdata/_tmp/db/test.sqlite',
				"prefix" => "excellent_test",
				"path_cache_dir" => __DIR__.'/testdata/_tmp/caches/',
			),
			// DB Table Definition.
			__DIR__.'/testdata/db/sample_db_tables.xlsx'
		);
		$this->assertTrue( is_object($exdb) );
	}//testMain()

}
