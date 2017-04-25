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
	 * INSERT
	 */
	public function insert($tbl, $data){
		$this->last_insert_info = false;//初期化
		$auto_column = false;
		$table_definition = $this->exdb->get_table_definition($tbl);
		// var_dump($table_definition);
		// var_dump($data);

		$sql = array();
		$sql['insert'] = 'INSERT INTO '.$this->exdb->get_physical_table_name($tbl).'(';
		$sql['values'] = ')VALUES(';
		$sql['close'] = ');';

		$sql_keys = array();
		$sql_tpls = array();

		foreach( $table_definition->table_definition as $column_definition ){
			array_push($sql_keys, $column_definition->column_name);
			array_push($sql_tpls, ':'.$column_definition->column_name);
			if( $column_definition->type == 'auto_id' ){
				$auto_column = array(
					'type'=>$column_definition->type,
					'column_name'=>$column_definition->column_name,
					'value'=>null,
				);
			}else if( $column_definition->type == 'auto_increment' ){
				$auto_column = array(
					'type'=>$column_definition->type,
					'column_name'=>$column_definition->column_name,
					'value'=>null,
				);
			}
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
			foreach( $table_definition->table_definition as $column_definition ){
				$row_value = null;
				if( !@is_null( $data[$column_definition->column_name] ) ){
					// データの入力がある場合
					$row_value = $data[$column_definition->column_name];
				}

				if( $column_definition->type == 'auto_id' ){
					if( is_null($row_value) ){
						$row_value = md5( microtime().'_'.rand() );
						$auto_column['value'] = $row_value;
					}
				}elseif( $column_definition->type == 'password' ){
					if( !is_null($row_value) ){
						$row_value = $this->exdb->encrypt_password($row_value);
					}
				}elseif( $column_definition->type == 'create_date' ){
					$row_value = date("Y-m-d H:i:s");
				}elseif( $column_definition->type == 'delete_flg' ){
					$row_value = 0;
				}

				$insert_data[':'.$column_definition->column_name] = $row_value;

			}
			// var_dump($insert_data);

			$result = $sth->execute($insert_data);
			if( $result === false ){
				continue;
			}

			break;
		}

		if( $auto_column['type'] == 'auto_increment' ){
			$auto_column['value'] = $this->pdo->lastInsertId();
		}
		$this->last_insert_info = $auto_column;

		return true;
	} // insert()

	/**
	 * 最後に挿入したレコードを引くためのキー情報を取得する
	 * @return Array キー情報を格納する連想配列
	 */
	public function get_last_insert_info(){
		return $this->last_insert_info;
	}

}
