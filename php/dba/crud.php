<?php
/**
 * excellent-db: DBA/CRUD
 */
namespace excellent_db;

/**
 * dba/crud.php
 */
class dba_crud{

	/** ExcellentDb Object */
	private $exdb;

	/** 最後に挿入したレコードを引くために必要な情報の記憶 */
	private $last_insert_info = false;

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
	 * INSERT文を発行する
	 * @param  string $tbl テーブル名
	 * @param  array $data 挿入するデータ
	 * @return boolean 実行結果の成否
	 */
	public function insert($tbl, $data){
		$this->last_insert_info = false;//初期化
		$table_definition = $this->exdb->get_table_definition($tbl);
		$auto_column = $table_definition->system_columns->id;
		@$auto_column->value = null;
		// var_dump($table_definition);
		// var_dump($data);

		$errors = $this->exdb->validate( $tbl, $data );
		if( count($errors) ){
			return false;
		}

		$sql = array();
		$sql['insert'] = 'INSERT INTO '.$this->exdb->get_physical_table_name($tbl).'(';
		$sql['values'] = ')VALUES(';
		$sql['close'] = ');';

		$sql_keys = array();
		$sql_tpls = array();

		foreach( $table_definition->columns as $column_definition ){
			array_push($sql_keys, $column_definition->name);
			array_push($sql_tpls, ':'.$column_definition->name);
		}

		$sql_template = $sql['insert'].implode($sql_keys,',').$sql['values'].implode($sql_tpls,',').$sql['close'];
		// var_dump($sql_template);
		$sth = $this->exdb->pdo()->prepare( $sql_template );

		$try_count = 0;
		while(1){
			$try_count ++;
			if( $try_count > 5 ){
				return false;
				break;
			}
			$insert_data = array();
			foreach( $table_definition->columns as $column_definition ){
				$row_value = null;
				if( !@is_null( $data[$column_definition->name] ) ){
					// データの入力がある場合
					$row_value = $data[$column_definition->name];
				}

				if( $column_definition->type == 'auto_id' ){
					// 文字列型IDを自動的に発行する
					if( is_null($row_value) ){
						$row_value = md5( microtime().'_'.rand() );
						$auto_column->value = $row_value;
					}
				}elseif( $column_definition->type == 'password' ){
					// パスワードを自動的にハッシュ値化する
					if( !is_null($row_value) ){
						$row_value = $this->exdb->encrypt_password($row_value);
					}
				}elseif( $column_definition->type == 'create_date' ){
					// 初回挿入日時を自動的にセットする
					$row_value = date("Y-m-d H:i:s");
				}elseif( $column_definition->type == 'delete_flg' ){
					// 論理削除フラグを自動的にセットする
					$row_value = 0;
				}

				$insert_data[':'.$column_definition->name] = $row_value;

			}
			// var_dump($insert_data);

			$result = $sth->execute($insert_data);
			if( $result === false ){
				continue;
			}

			break;
		}

		if( $auto_column->type == 'auto_increment' ){
			$auto_column->value = $this->pdo->lastInsertId();
		}
		$this->last_insert_info = $auto_column;
		@$this->last_insert_info->table_name = $tbl;
		@$this->last_insert_info->insert_data = $data;

		return true;
	} // insert()

	/**
	 * 最後に挿入したレコードを引くためのキー情報を取得する
	 * @return Array キー情報を格納する連想配列
	 */
	public function get_last_insert_info(){
		return $this->last_insert_info;
	} // get_last_insert_info()

	/**
	 * SELECT文を発行する
	 * @param  string $tbl テーブル名
	 * @param  array $where 抽出条件
	 * @param  array $options オプション(連想配列)
	 * - *page* : `1` から始まるページ番号 (default: `1`)
	 * - *limit* : 1ページあたりの件数 (default: `10`)
	 * @return array 抽出されたレコード
	 */
	public function select($tbl, $where, $options = array()){
		$table_definition = $this->exdb->get_table_definition($tbl);
		$delete_flg_id = $table_definition->system_columns->delete_flg;
		// var_dump($table_definition);
		// var_dump($options);

		$page = 1; // ページ数(0から数える)
		if( @strlen($options['page']) ){
			$page = intval($options['page']);
		}
		if( $page < 1 ){ $page = 1; }

		$limit = 10; // 10件ずつ
		if( @strlen($options['limit']) ){
			$limit = intval($options['limit']);
		}
		if( $limit < 1 ){ $limit = 1; }

		foreach($where as $key=>$val){
			if( $table_definition->columns->{$key}->type == 'password' ){
				// パスワードをハッシュ値化
				$where[$key] = $this->exdb->encrypt_password($val);
			}elseif( $table_definition->columns->{$key}->type == 'delete_flg' ){
				// delete_flgを 0 or 1 に整形
				$where[$key] = ($val ? 1 : 0);
			}
		}
		if( is_string($delete_flg_id) && @is_null( $where[$delete_flg_id] ) ){
			$where[$delete_flg_id] = 0;
		}

		$sql = array();
		$sql['select'] = 'SELECT * FROM '.$this->exdb->get_physical_table_name($tbl).'';
		$sql['where'] = ' WHERE ';
		$sql['limit'] = ' LIMIT '.(($page-1)*$limit).','.$limit.' ';
		$sql['close'] = ';';

		$sql_where = array();
		foreach( $where as $column_name => $cond ){
			array_push($sql_where, $column_name.'=:'.$column_name);
		}

		$sql_template = $sql['select'].(count($sql_where) ? $sql['where'].implode($sql_where, ' AND ') : '').$sql['limit'].$sql['close'];
		// var_dump($sql_template);
		$sth = $this->exdb->pdo()->prepare( $sql_template );
		if( !$sth ){
			return false;
		}
		$result = $sth->execute( $where );
		if( $result === false ){
			return false;
		}
		$result = $sth->fetchAll(\PDO::FETCH_ASSOC);
		// var_dump($result);

		return $result;
	} // select()

	/**
	 * SELECT文を発行し、該当件数を調べる
	 * @param  string $tbl テーブル名
	 * @param  array $where 抽出条件
	 * @return int 抽出されたレコード数
	 */
	public function count($tbl, $where){
		$table_definition = $this->exdb->get_table_definition($tbl);
		$delete_flg_id = $table_definition->system_columns->delete_flg;
		// var_dump($table_definition);

		foreach($where as $key=>$val){
			if( $table_definition->columns->{$key}->type == 'password' ){
				// パスワードをハッシュ値化
				$where[$key] = $this->exdb->encrypt_password($val);
			}elseif( $table_definition->columns->{$key}->type == 'delete_flg' ){
				// delete_flgを 0 or 1 に整形
				$where[$key] = ($val ? 1 : 0);
			}
		}
		if( is_string($delete_flg_id) && @is_null( $where[$delete_flg_id] ) ){
			$where[$delete_flg_id] = 0;
		}

		$sql = array();
		$sql['select'] = 'SELECT COUNT(*) AS count FROM '.$this->exdb->get_physical_table_name($tbl).'';
		$sql['where'] = ' WHERE ';
		$sql['close'] = ';';

		$sql_where = array();
		foreach( $where as $column_name => $cond ){
			array_push($sql_where, $column_name.'=:'.$column_name);
		}

		$sql_template = $sql['select'].(count($sql_where) ? $sql['where'].implode($sql_where, ' AND ') : '').$sql['close'];
		// var_dump($sql_template);
		$sth = $this->exdb->pdo()->prepare( $sql_template );
		$result = $sth->execute( $where );
		if( $result === false ){
			return false;
		}
		$result = $sth->fetchAll(\PDO::FETCH_ASSOC);
		// var_dump($result);
		$rtn = intval($result[0]['count']);

		return $rtn;
	} // count()

	/**
	 * UPDATE文を発行する
	 * @param  string $tbl テーブル名
	 * @param  array $where 抽出条件
	 * @param  array $data 更新するデータ
	 * @return int 変更が反映されたレコード数
	 */
	public function update($tbl, $where, $data){
		$table_definition = $this->exdb->get_table_definition($tbl);
		$delete_flg_id = $table_definition->system_columns->delete_flg;

		foreach($table_definition->columns as $key=>$val){
			if( !array_key_exists( $key, $where ) && !array_key_exists( $key, $data ) ){continue;}
			if( $table_definition->columns->{$key}->type == 'password' ){
				// パスワードをハッシュ値化
				if( array_key_exists($key, $where) ){ $where[$key] = $this->exdb->encrypt_password($where[$key]); }
				if( strlen($data[$key]) ){
					if( array_key_exists($key, $data) ){ $data[$key] = $this->exdb->encrypt_password($data[$key]); }
				}else{
					unset($data[$key]);
				}
			}elseif( $table_definition->columns->{$key}->type == 'delete_flg' ){
				// delete_flgを 0 or 1 に整形
				if( array_key_exists($key, $where) ){ $where[$key] = ($where[$key] ? 1 : 0); }
				if( array_key_exists($key, $data) ){ $data[$key] = ($data[$key] ? 1 : 0); }
			}
		}
		if( is_string($delete_flg_id) && @is_null( $where[$delete_flg_id] ) ){
			$where[$delete_flg_id] = 0;
		}

		$errors = $this->exdb->validate( $tbl, $data );
		if( count($errors) ){
			return false;
		}

		$sql = array();
		$sql['update'] = 'UPDATE '.$this->exdb->get_physical_table_name($tbl).'';
		$sql['set'] = ' SET ';
		$sql['where'] = ' WHERE ';
		$sql['close'] = ';';

		$bind_data = array();

		$sql_set = array();
		$is_update_date = false;
		foreach( $data as $column_name => $data_row ){
			array_push($sql_set, $column_name.'=:__set__'.$column_name);
			$bind_data['__set__'.$column_name] = $data_row;
			if( $table_definition->system_columns->update_date === $column_name ){
				$is_update_date = true;
			}
		}

		$sql_where = array();
		foreach( $where as $column_name => $cond ){
			array_push($sql_where, $column_name.'=:__where__'.$column_name);
			$bind_data['__where__'.$column_name] = $cond;
		}

		if($table_definition->system_columns->update_date){
			// 最終更新日時を自動的にセットする
			$bind_data[':__set__'.$table_definition->system_columns->update_date] = date("Y-m-d H:i:s");
			if( !$is_update_date ){
				array_push($sql_set, $table_definition->system_columns->update_date.'=:__set__'.$table_definition->system_columns->update_date);
			}
		}


		$sql_template = $sql['update'].(count($sql_set) ? $sql['set'].implode($sql_set, ', ') : '').(count($sql_where) ? $sql['where'].implode($sql_where, ' AND ') : '').$sql['close'];
		// var_dump($sql_template);
		$sth = $this->exdb->pdo()->prepare( $sql_template );
		$result = $sth->execute( $bind_data );
		if( $result === false ){
			return false;
		}
		$result = $sth->rowCount();
		// var_dump($result);

		return $result;
	} // update()

	/**
	 * DELETE文を発行する (Logical Deletion)
	 *
	 * このメソッドは、 `$where` に指定された条件のレコードを削除します。
	 * テーブル定義に `delete_flg` が含まれる場合は論理削除、 含まれない場合は 物理削除します。
	 *
	 * @param  string $tbl テーブル名
	 * @param  array $where 抽出条件
	 * @return int 変更が反映されたレコード数
	 */
	public function delete($tbl, $where){
		$table_definition = $this->exdb->get_table_definition($tbl);
		$delete_flg_id = $table_definition->system_columns->delete_flg;
		$delete_date_id = $table_definition->system_columns->delete_date;
		$result = 0;

		if( strlen($delete_flg_id) ){
			// 論理削除フラグを持っているテーブルでは、論理削除する。
			$data = array(
				$delete_date_id => date("Y-m-d H:i:s") ,
				$delete_flg_id => 1 ,
			);
			// var_dump($data);
			$result = $this->update($tbl, $where, $data);
		}else{
			// 論理削除フラグがない場合は、物理削除する。
			$result = $this->physical_delete($tbl, $where);
		}
		return $result;
	} // delete()

	/**
	 * DELETE文を発行する (Physical Deletion)
	 * @param  string $tbl テーブル名
	 * @param  array $where 抽出条件
	 * @return int 変更が反映されたレコード数
	 */
	public function physical_delete($tbl, $where){
		$table_definition = $this->exdb->get_table_definition($tbl);

		$sql = array();
		$sql['delete'] = 'DELETE FROM '.$this->exdb->get_physical_table_name($tbl).'';
		$sql['where'] = ' WHERE ';
		$sql['close'] = ';';

		$bind_data = array();
		$sql_where = array();
		foreach( $where as $column_name => $cond ){
			array_push($sql_where, $column_name.'=:__where__'.$column_name);
			$bind_data['__where__'.$column_name] = $cond;
		}

		$sql_template = $sql['delete'].(count($sql_where) ? $sql['where'].implode($sql_where, ' AND ') : '').$sql['close'];
		// var_dump($sql_template);
		$sth = $this->exdb->pdo()->prepare( $sql_template );
		$result = $sth->execute( $bind_data );
		if( $result === false ){
			return false;
		}
		$result = $sth->rowCount();
		// var_dump($result);

		return $result;
	} // physical_delete()

}
