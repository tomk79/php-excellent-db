<?php
/**
 * Test Script
 */
class clearningTest extends PHPUnit_Framework_TestCase{

	/**
	 * setup
	 */
	public function setup(){
	}

	/**
	 * clearning
	 */
	public function testClearning(){
		$path_db = __DIR__.'/testdata/_tmp/db/test.sqlite';
		@unlink($path_db);

		clearstatcache();
		$this->assertFalse( is_file($path_db) );

	}//testClearning()

}
