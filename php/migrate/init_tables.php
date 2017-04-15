<?php
/**
 * excellent-db: Migration
 */
namespace excellent_db;

/**
 * migrate/init_tables.php
 */
class migrate_init_tables{

	/** ExcellentDb Object */
	private $exdb;

	/**
	 * constructor
	 *
	 * @param object $exdb ExcellentDb Object
	 */
	public function __construct( $exdb ){
		$this->exdb = $exdb;
		return;
	}

	/**
	 * データベーステーブルを初期化する
	 * @return boolean 成否。
	 */
	public function init(){
		return true;
	}

}
