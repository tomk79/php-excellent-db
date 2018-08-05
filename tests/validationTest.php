<?php
/**
 * Test Script
 */
class validationTest extends PHPUnit_Framework_TestCase{

	private $validator;

	/**
	 * setup
	 */
	public function setup(){
		mb_internal_encoding('utf-8');

		$path_db = __DIR__.'/testdata/_tmp/db/test.sqlite';
		$pdo = new \PDO(
			'sqlite:'.$path_db,
			null, null,
			array(
				\PDO::ATTR_PERSISTENT => false, // ←これをtrueにすると、"持続的な接続" になる
			)
		);

		$exdb = new excellent_db\create( $pdo, array(
			"prefix" => "excellent_test",
			"path_definition_file" => __DIR__.'/testdata/db/sample_db_tables.xlsx',
			"path_cache_dir" => __DIR__.'/testdata/_tmp/caches/',
		) );

		$this->validator = $exdb->validator();
	}

	/**
	 * Not Null Restrections
	 */
	public function testValidateNotNull(){

		$errors = $this->validator->detect_errors('', 'email', array('not_null'=>true));
		// var_dump($errors);
		$this->assertEquals( count($errors), 1 );

		$errors = $this->validator->detect_errors('', 'email', array('not_null'=>false));
		// var_dump($errors);
		$this->assertEquals( count($errors), 0 );

		$errors = $this->validator->detect_errors('valid@example.com', 'email', array('not_null'=>true));
		// var_dump($errors);
		$this->assertEquals( count($errors), 0 );

	}//testValidateNotNull()

	/**
	 * Text format check
	 */
	public function testValidateText(){

		$errors = $this->validator->detect_errors('valid', 'text');
		// var_dump($errors);
		$this->assertEquals( count($errors), 0 );

		$errors = $this->validator->detect_errors('valid', 'textarea');
		// var_dump($errors);
		$this->assertEquals( count($errors), 0 );

		$errors = $this->validator->detect_errors('invalid'."\r\n"."invalid", 'text');
		// var_dump($errors);
		$this->assertEquals( count($errors), 1 );

		$errors = $this->validator->detect_errors('valid'."\r\n"."valid", 'textarea');
		// var_dump($errors);
		$this->assertEquals( count($errors), 0 );

	}

	/**
	 * Email format check
	 */
	public function testValidateEmails(){

		$errors = $this->validator->detect_errors('valid@example.com', 'email');
		// var_dump($errors);
		$this->assertEquals( count($errors), 0 );

		$errors = $this->validator->detect_errors('invalid.example.com', 'email');
		// var_dump($errors);
		$this->assertEquals( count($errors), 1 );

	}//testValidateEmails()

}
