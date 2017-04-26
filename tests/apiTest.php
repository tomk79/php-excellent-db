<?php
// Command that starts the built-in web server
$command = sprintf(
	'php -S %s:%d -t %s >/dev/null 2>&1 & echo $!',
	WEB_SERVER_HOST,
	WEB_SERVER_PORT,
	WEB_SERVER_DOCROOT
);

// Execute the command and store the process ID
$output = array();
exec($command, $output);
$pid = (int) $output[0];

echo sprintf(
	'%s - Web server started on %s:%d with PID %d',
	date('r'),
	WEB_SERVER_HOST,
	WEB_SERVER_PORT,
	$pid
) . PHP_EOL;

// Kill the web server when the process ends
register_shutdown_function(function() use ($pid) {
	echo sprintf('%s - Killing process with ID %d', date('r'), $pid) . PHP_EOL;
	exec('kill ' . $pid);
});


/**
 * Test Script
 */
class apiTest extends PHPUnit_Framework_TestCase{

	private $client;

	/**
	 * setup
	 */
	public function setup(){
		mb_internal_encoding('utf-8');
		@date_default_timezone_set('Asia/Tokyo');

		$this->client = new \GuzzleHttp\Client();
	}

	/**
	 * Web Server Health Check
	 */
	public function testWebServerHealthCheck(){

		$res = $this->client->request('GET', 'http://'.WEB_SERVER_HOST.':'.WEB_SERVER_PORT.'/healthcheck.html');
		// var_dump($res);
		$this->assertEquals( trim($res->getBody()), 'health check.' );

	}//testWebServerHealthCheck()

	/**
	 * POST
	 */
	public function testPost(){

		$res = $this->client->request(
			'POST',
			'http://'.WEB_SERVER_HOST.':'.WEB_SERVER_PORT.'/api_test.php/user',
			array(
				'form_params'=>array(
					'user_account' => 'post-tester-00001',
					'password' => 'password',
					'user_name' => 'POST Tester No.00001',
				)
			)
		);
		// var_dump($res);
		$postJson = json_decode($res->getBody());
		// var_dump($postJson);
		$this->assertEquals( $postJson->result, true );

		// GET して確認
		$res = $this->client->request(
			'GET',
			'http://'.WEB_SERVER_HOST.':'.WEB_SERVER_PORT.'/api_test.php/user/'.$postJson->given_id
		);
		$getJson = json_decode($res->getBody());
		// var_dump($getJson);
		$this->assertEquals( $getJson->result, true );
		$this->assertEquals( $getJson->row->user_id, $postJson->given_id );
		$this->assertEquals( $getJson->row->user_account, 'post-tester-00001' );
		$this->assertEquals( @$getJson->row->password, null );
		$this->assertEquals( $getJson->row->user_name, 'POST Tester No.00001' );

	}//testPost()

	/**
	 * Gettin List
	 */
	public function testGettingList(){

		$res = $this->client->request('GET', 'http://'.WEB_SERVER_HOST.':'.WEB_SERVER_PORT.'/api_test.php/user');
		// var_dump($res);
		$json = json_decode($res->getBody());
		// var_dump($json);
		$this->assertEquals( $json->result, true );

	}//testGettingList()

}
