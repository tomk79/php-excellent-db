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
		$exdb = new excellent_db\main();
		$this->assertTrue( is_object($exdb) );
	}//testMain()

}
