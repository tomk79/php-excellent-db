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
	 * @param  string $table_name テーブル名
	 * @return boolean If logged in return `true`, logged out return `false`.
	 */
	public function is_login( $table_name ){
		$user_info = $this->get_login_user_info( $table_name );
		if( $user_info === false ){
			return false;
		}
		return true;
	}

	/**
	 * Get Login User Info.
	 *
	 * @param  string $table_name テーブル名
	 * @return mixed If logged in return array, logged out return `false`.
	 */
	public function get_login_user_info( $table_name ){
		$user_info = $this->exdb->session()->get('user_info');
		if( is_array( @$user_info[$table_name] ) ){
			return $user_info[$table_name];
		}
		return false;
	}

	/**
	 * ログインする
	 * @param  string $table_name テーブル名
	 * @param  array $inquiries 照会するカラム名
	 * @param  array $data      ユーザーの入力データ
	 * @return boolean result.
	 */
	public function login( $table_name, $inquiries, $data ){
		// var_dump($table_name, $inquiries, $data);
		$where = array();
		foreach( $inquiries as $key ){
			$where[$key] = @$data[$key];
		}
		$res = $this->exdb->select( $table_name, $where );
		if( is_array($res) && count($res) === 1 ){
			$user_info = $this->exdb->session()->get('user_info');
			if(!is_array($user_info)){
				$user_info = array();
			}
			$user_info[$table_name] = $res[0];
			$this->exdb->session()->set('user_info', $user_info);
			return true;
		}
		return false;
	}

	/**
	 * ログアウトする
	 * @param  string $table_name テーブル名
	 * @return boolean result.
	 */
	public function logout( $table_name ){
		$user_info = $this->exdb->session()->get('user_info');
		if(!is_array($user_info)){
			$user_info = array();
		}
		unset($user_info[$table_name]);
		$this->exdb->session()->set('user_info', $user_info);
		return true;
	}

}
