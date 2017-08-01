<?php
require_once(__DIR__.'/testhelper/server_setup.php');
test_helper_server_setup();

/**
 * Test Script
 */
class apiTest extends PHPUnit_Framework_TestCase{

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

		$res = $this->client->request('GET', 'http://'.WEB_SERVER_HOST.':'.WEB_SERVER_PORT.'/healthcheck.html');
		// var_dump($res);
		$this->assertEquals( trim($res->getBody()), 'health check.' );

	}//testWebServerHealthCheck()

	/**
	 * POST, PUT, DELETE
	 */
	public function testPostPutDelete(){

		// --------------------------------------
		// 行を追加
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
		$this->assertTrue( is_string($postJson->given_id) );
		$this->assertTrue( strlen($postJson->given_id) == strlen(md5('.')) );

		// GET して確認
		// 注意: テーブルに `key_column` が指定されている場合、指定されたカラム値で引く。
		// 　　　↓この場合は `user_account` 。
		$res = $this->client->request(
			'GET',
			'http://'.WEB_SERVER_HOST.':'.WEB_SERVER_PORT.'/api_test.php/user/post-tester-00001'
		);
		$getJson = json_decode($res->getBody());
		// var_dump($getJson);
		$this->assertEquals( $getJson->result, true );
		$this->assertEquals( $getJson->row->user_id, $postJson->given_id );
		$this->assertEquals( $getJson->row->user_account, 'post-tester-00001' );
		$this->assertNull( @$getJson->row->password );
		$this->assertEquals( $getJson->row->user_name, 'POST Tester No.00001' );
		$this->assertFalse( is_string($getJson->row->update_date) );


		// --------------------------------------
		// 行を更新
		$res = $this->client->request(
			'PUT',
			'http://'.WEB_SERVER_HOST.':'.WEB_SERVER_PORT.'/api_test.php/user/post-tester-00001',
			array(
				'form_params'=>array(
					'user_account' => 'post-and-put-tester-00001',
					'password' => 'password',
					'user_name' => 'POST and PUT Tester No.00001',
				)
			)
		);
		// var_dump($res);
		$putJson = json_decode($res->getBody());
		// var_dump($putJson);
		$this->assertEquals( $putJson->result, true );
		$this->assertEquals( $putJson->affected_rows, 1 );

		// GET して確認
		$res = $this->client->request(
			'GET',
			'http://'.WEB_SERVER_HOST.':'.WEB_SERVER_PORT.'/api_test.php/user/post-tester-00001'
		);
		$getJson = json_decode($res->getBody());
		// var_dump($getJson);
		$this->assertEquals( $getJson->result, true );
		$this->assertEquals( $getJson->row->user_id, $postJson->given_id );
		$this->assertEquals( $getJson->row->user_account, 'post-tester-00001' );
		$this->assertNull( @$getJson->row->password );
		$this->assertEquals( $getJson->row->user_name, 'POST Tester No.00001' );
		$this->assertTrue( is_string($getJson->row->update_date) );


		// --------------------------------------
		// 追加された行を削除
		$res = $this->client->request(
			'DELETE',
			'http://'.WEB_SERVER_HOST.':'.WEB_SERVER_PORT.'/api_test.php/user/post-tester-00001'
		);
		// var_dump($res);
		$deleteJson = json_decode($res->getBody());
		// var_dump($deleteJson);
		$this->assertEquals( $deleteJson->result, true );
		$this->assertEquals( $deleteJson->affected_rows, 1 );

		// GET して確認
		$res = $this->client->request(
			'GET',
			'http://'.WEB_SERVER_HOST.':'.WEB_SERVER_PORT.'/api_test.php/user/post-tester-00001'
		);
		$getJson = json_decode($res->getBody());
		// var_dump($getJson);
		$this->assertEquals( $getJson->result, true );
		$this->assertNull( $getJson->row );

	} // testPostPutDelete()

	/**
	 * POST Error Test
	 */
	public function testPostErrors(){

		// --------------------------------------
		// 行を追加
		// 必須項目を省略するエラー
		$res = $this->client->request(
			'POST',
			'http://'.WEB_SERVER_HOST.':'.WEB_SERVER_PORT.'/api_test.php/user',
			array(
				'form_params'=>array(
					// 'user_account' => 'post-tester-00001', // <- required value
					'password' => 'password',
					'user_name' => 'POST Tester No.00001',
				)
			)
		);
		// var_dump($res);
		$postJson = json_decode($res->getBody());
		// var_dump($postJson);
		$this->assertFalse( $postJson->result );
		$this->assertNull( $postJson->given_id );

		$res = $this->client->request(
			'POST',
			'http://'.WEB_SERVER_HOST.':'.WEB_SERVER_PORT.'/api_test.php/user',
			array(
				'form_params'=>array(
					'user_account' => 'post-error-tester-00001', // <- required value
					'password' => 'password', // <- required value
					// 'user_name' => 'POST Error Tester No.00001', // <- required value
				)
			)
		);
		// var_dump($res);
		$postJson = json_decode($res->getBody());
		// var_dump($postJson);
		$this->assertFalse( $postJson->result );
		$this->assertNull( $postJson->given_id );

		// --------------------------------------
		// UNIQUE項目が重複しているエラー
		$res = $this->client->request(
			'POST',
			'http://'.WEB_SERVER_HOST.':'.WEB_SERVER_PORT.'/api_test.php/user',
			array(
				'form_params'=>array(
					'user_account' => 'post-tester-00001', // <- UNIQUE (先に論理削除のテストをしているためDBには残っている)
					'password' => 'password',
					'user_name' => 'POST Tester No.00001',
				)
			)
		);
		// var_dump($res);
		$postJson = json_decode($res->getBody());
		// var_dump($postJson);
		$this->assertFalse( $postJson->result );
		$this->assertNull( $postJson->given_id );

		// --------------------------------------
		// Emailの形式エラー
		$res = $this->client->request(
			'POST',
			'http://'.WEB_SERVER_HOST.':'.WEB_SERVER_PORT.'/api_test.php/user',
			array(
				'form_params'=>array(
					'user_account' => 'post-error-tester-00003',
					'password' => 'password',
					'user_name' => 'POST Error Tester No.00003',
					'email' => 'testmail.localhost', // 記号 `@` が含まれないため、Email形式のバリデータがエラーを返すはず
				)
			)
		);
		// var_dump($res);
		$postJson = json_decode($res->getBody());
		// var_dump($postJson);
		$this->assertFalse( $postJson->result );
		$this->assertNull( $postJson->given_id );

	} // testPostErrors()

	/**
	 * Getting List
	 */
	public function testGettingList(){

		$res = $this->client->request('GET', 'http://'.WEB_SERVER_HOST.':'.WEB_SERVER_PORT.'/api_test.php/user');
		// var_dump($res);
		$json = json_decode($res->getBody());
		// var_dump($json);
		$this->assertEquals( $json->result, true );
		$this->assertTrue( is_array($json->list) );
		$this->assertEquals( count($json->list), 10 );
		$this->assertTrue( is_object($json->list[0]) );
		$this->assertNull( @$json->list[0]->password );
		$this->assertNull( @$json->list[1]->password );
		$this->assertNull( @$json->list[2]->password );
		$this->assertEquals( @$json->list[0]->user_account, 'tester-00001' );
		$this->assertEquals( @$json->list[1]->user_account, 'tester-00002' );
		$this->assertEquals( @$json->list[2]->user_account, 'tester-00003' );
		$this->assertEquals( @$json->count, 499 );

		$res = $this->client->request('GET', 'http://'.WEB_SERVER_HOST.':'.WEB_SERVER_PORT.'/api_test.php/user?:page=2&:limit=100');
		// var_dump($res);
		$json = json_decode($res->getBody());
		// var_dump($json);
		$this->assertEquals( $json->result, true );
		$this->assertTrue( is_array($json->list) );
		$this->assertEquals( count($json->list), 100 );
		$this->assertTrue( is_object($json->list[0]) );
		$this->assertNull( @$json->list[0]->password );
		$this->assertNull( @$json->list[1]->password );
		$this->assertNull( @$json->list[2]->password );
		$this->assertEquals( @$json->list[0]->user_account, 'tester-00101' );
		$this->assertEquals( @$json->list[1]->user_account, 'tester-00102' );
		$this->assertEquals( @$json->list[2]->user_account, 'tester-00103' );
		$this->assertEquals( @$json->count, 499 );

	}//testGettingList()

}
