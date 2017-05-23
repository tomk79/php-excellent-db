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
		$table_definition = $this->exdb->get_table_definition_all();
		// var_dump($table_definition);
		$error = array();
		foreach($table_definition->tables as $table_name=>$table_definition_row){
			// var_dump($table_definition_row);

			$sql_create_db = '';
			$sql_create_db .= 'CREATE TABLE '.$this->exdb->get_physical_table_name($table_definition_row->name).' (';
			$ary_table_columns = array();
			foreach( $table_definition_row->columns as $column_definition ){
				$sql_column_definition = '';

				// セル名
				$sql_column_definition .= $column_definition->name;

				// 型
				switch(strtolower( $column_definition->type )){
					case 'email':
					case 'string':
					case 'password':
					case 'auto_id':
						$sql_column_definition .= ' VARCHAR';
						break;
					case 'create_date':
					case 'update_date':
					case 'delete_date':
						$sql_column_definition .= ' DATETIME';
						break;
					case 'int':
					case 'integer':
					case 'number':
					case 'auto_increment':
						$sql_column_definition .= ' INTEGER';
						break;
					case 'delete_flg':
						$sql_column_definition .= ' INTEGER';
						break;
					case 'text':
					case 'date':
					case 'datetime':
					default:
						$sql_column_definition .= ' '.strtoupper($column_definition->type);
						break;
				}

				// NOT NULL 制約
				if( $column_definition->not_null ){
					$sql_column_definition .= ' NOT NULL';
				}

				// UNIQUE 制約
				if( $column_definition->unique ){
					$sql_column_definition .= ' UNIQUE';
				}

				array_push($ary_table_columns, $sql_column_definition);
			}
			$sql_create_db .= implode($ary_table_columns, ',');
			$sql_create_db .= ');';
			// var_dump($sql_create_db);
			$result = @$this->exdb->pdo()->query($sql_create_db);
			// var_dump($result);
			if( !$result ){
				array_push($error, '[ERROR] Failed to create table "'.$this->exdb->get_physical_table_name($table_definition_row->name).'"');
			}
		}
		// var_dump($error);
		$rtn = true;
		if(count($error)){$rtn = false;}
		return $rtn;
	}

}
