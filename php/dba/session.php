<?php
/**
 * excellent-db: DBA/Session Manager
 */
namespace excellent_db;

/**
 * dba/session.php
 */
class dba_session{

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
	 * セッションを初期化する
	 * @return boolean result.
	 */
	private function initialize_session(){
		if(!@is_array( $_SESSION )){
			session_start();
		}
		return @is_array( $_SESSION );
	}

	/**
	 * セッション値を保存する
	 * @param  string $key セッションキー
	 * @param  mixed $val 値
	 * @return boolean Always `true`.
	 */
	public function set( $key, $val ){
		$this->initialize_session();
		$_SESSION[$key] = $val;
		return true;
	}

	/**
	 * セッション値を取り出す
	 * @param  string $key セッションキー
	 * @return mixed セッション値
	 */
	public function get( $key ){
		$this->initialize_session();
		return @$_SESSION[$key];
	}

}
