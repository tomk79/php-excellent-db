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

		$res = $this->client->request('GET', 'http://'.WEB_SERVER_HOST.':'.WEB_SERVER_PORT.'/api_test.php');
		// var_dump($res);
		$this->assertEquals( trim($res->getBody()), 'test' );

	}//testWebServerHealthCheck()

}
