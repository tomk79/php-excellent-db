<?php
require_once(__DIR__.'/testhelper/server_setup.php');
test_helper_server_setup();

/**
 * Test Script
 */
class formTest extends PHPUnit_Framework_TestCase{

	private $client;
	private $posted_id;

	/**
	 * setup
	 */
	public function setup(){
		mb_internal_encoding('utf-8');

		$this->client = new \GuzzleHttp\Client();
	}

	/**
	 * Web Server Health Check
	 */
	public function testWebServerHealthCheck(){

		$res = $this->client->request('GET', 'http://'.WEB_SERVER_HOST.':'.WEB_SERVER_PORT.'/form_test.php');
		// var_dump($res);
		$this->assertTrue( !!preg_match('/\!DOCTYPE html/', $res->getBody()) );

	}//testWebServerHealthCheck()

}
