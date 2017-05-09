<?php
/**
 * excellent-db
 */
namespace excellent_db;

/**
 * user.php - Login User Manager
 */
class user{

	/** Excellent Db Instance */
	private $exdb;

	/**
	 * constructor
	 *
	 * @param object $exdb Excellent Db Instance
	 */
	public function __construct( $exdb ){
		$this->exdb = $exdb;
		return;
	}

	/**
	 * Check User Logging Status.
	 *
	 * @return boolean If logged in return `true`, logged out return `false`.
	 */
	public function is_login(){
		// TODO: 未実装
		return false;
	}

}
