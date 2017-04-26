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
		$max_user_count = 2000;

		// --------------------------------------
		// ユーザーテーブルにデータを挿入
		for( $i = 0; $i < $max_user_count; $i ++ ){
			$str_id_number = str_pad( $i, 5, '0', STR_PAD_LEFT );
			$result_insert = $this->exdb->insert('user', array(
				'user_account'=>'tester-'.$str_id_number,
				'password'=>'password',
				'user_name'=>'Tester No.'.$str_id_number,
			));
			// var_dump($result_insert);
			$this->assertTrue( $result_insert );
			$last_insert_info = $this->exdb->get_last_insert_info();
			// var_dump($last_insert_info);
			$this->assertEquals( $last_insert_info->type, 'auto_id' );

			$userData = $this->exdb->select('user', array($last_insert_info->column_name=>$last_insert_info->value));
			// var_dump($userData);
			$this->assertEquals( count($userData), 1 );
			$this->assertEquals( $userData[0]['user_name'], 'Tester No.'.$str_id_number );

			$last_insert_row = $this->exdb->get_last_insert_row();
			// var_dump($last_insert_row);
			$this->assertEquals( $last_insert_row['user_name'], 'Tester No.'.$str_id_number );
		}

	}//testInsert()

	/**
	 * SELECT
	 */
	public function testSelect(){
		$max_user_count = 2000;

		// --------------------------------------
		// SELECT して答え合わせ
		$userList = $this->exdb->select('user', array());
		// var_dump($userList);
		$this->assertEquals( count($userList), $max_user_count );

	}//testSelect()

	/**
	 * UPDATE
	 */
	public function testUpdate(){
		$result = $this->exdb->update(
			'user',
			array(
				'user_account'=>'tester-00000'
			),
			array(
				'user_name'=>'Updated UserName No.00000'
			)
		);
		// var_dump($result);
		$this->assertEquals( $result, 1 );

		$afterData = $this->exdb->select('user', array('user_account'=>'tester-00000'));
		// var_dump($afterData);
		$this->assertTrue( is_string($afterData[0]['update_date']) );
		$this->assertEquals( $afterData[0]['user_name'], 'Updated UserName No.00000' );
	}//testUpdate()

	/**
	 * DELETE (Logical Deletion)
	 */
	public function testDelete(){
		$result = $this->exdb->delete(
			'user',
			array(
				'user_account'=>'tester-00000'
			)
		);
		// var_dump($result);
		$this->assertEquals( $result, 1 );

		$afterData = $this->exdb->select('user', array('user_account'=>'tester-00000'));
		// var_dump($afterData);
		$this->assertEquals( count($afterData), 0 );

		$afterData = $this->exdb->select('user', array('user_account'=>'tester-00000','delete_flg'=>1));
		// var_dump($afterData);
		$this->assertEquals( count($afterData), 1 );

	}//testDelete()

	/**
	 * DELETE (Physical Deletion)
	 */
	public function testPhysicalDelete(){
		$result = $this->exdb->physical_delete(
			'user',
			array(
				'user_account'=>'tester-00000'
			)
		);
		// var_dump($result);
		$this->assertEquals( $result, 1 );

		$afterData = $this->exdb->select('user', array('user_account'=>'tester-00000'));
		// var_dump($afterData);
		$this->assertEquals( count($afterData), 0 );

		$afterData = $this->exdb->select('user', array('user_account'=>'tester-00000','delete_flg'=>1));
		// var_dump($afterData);
		$this->assertEquals( count($afterData), 0 );

	}//testPhysicalDelete()

}
