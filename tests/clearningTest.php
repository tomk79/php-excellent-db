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
		$fs = new \tomk79\filesystem();

		$path_db = __DIR__.'/testdata/_tmp/db/test.sqlite';
		@unlink($path_db);

		$fs->rm(__DIR__.'/testdata/_tmp/caches/twig_cache/');

		clearstatcache();
		$this->assertFalse( is_file($path_db) );

	}//testClearning()

}
