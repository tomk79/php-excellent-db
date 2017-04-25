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
		$table_definition = $this->exdb->get_table_definition($tbl);
		// var_dump($table_definition);
		// var_dump($data);

		$sql = array();
		$sql['insert'] = 'INSERT INTO '.$this->exdb->get_physical_table_name($tbl).'(';
		$sql['values'] = ')VALUES(';
		$sql['close'] = ');';

		$sql_keys = array();
		$sql_tpls = array();

		foreach( $table_definition->table_definition as $cell_definition ){
			array_push($sql_keys, $cell_definition->cell_name);
			array_push($sql_tpls, ':'.$cell_definition->cell_name);
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
			foreach( $table_definition->table_definition as $cell_definition ){
				$row_value = null;
				if( !@is_null( $data[$cell_definition->cell_name] ) ){
					// データの入力がある場合
					$row_value = $data[$cell_definition->cell_name];
				}

				if( $cell_definition->type == 'auto_id' ){
					if( is_null($row_value) ){
						$row_value = md5( microtime().'_'.rand() );
					}
				}elseif( $cell_definition->type == 'password' ){
					if( !is_null($row_value) ){
						$row_value = $this->exdb->encrypt_password($row_value);
					}
				}elseif( $cell_definition->type == 'create_date' ){
					$row_value = date("Y-m-d H:i:s");
				}elseif( $cell_definition->type == 'delete_flg' ){
					$row_value = 0;
				}

				$insert_data[':'.$cell_definition->cell_name] = $row_value;

			}
			// var_dump($insert_data);

			$result = $sth->execute($insert_data);
			if( $result === false ){
				continue;
			}

			break;
		}

		return true;
	} // insert()

}
