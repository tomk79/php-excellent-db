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
			$sql_create_db .= 'CREATE TABLE '.$this->exdb->get_physical_table_name($table_definition_row->table_name).' (';
			$ary_table_cells = array();
			foreach( $table_definition_row->table_definition as $cell_definition ){
				$sql_cell_definition = '';

				// セル名
				$sql_cell_definition .= $cell_definition->cell_name;

				// 型
				switch(strtolower( $cell_definition->type )){
					case 'email':
					case 'string':
					case 'password':
					case 'auto_id':
						$sql_cell_definition .= ' VARCHAR';
						break;
					case 'create_date':
					case 'update_date':
					case 'delete_date':
						$sql_cell_definition .= ' DATETIME';
						break;
					case 'int':
					case 'integer':
					case 'number':
					case 'auto_increment':
						$sql_cell_definition .= ' INTEGER';
						break;
					case 'delete_flg':
						$sql_cell_definition .= ' INTEGER';
						break;
					case 'text':
					case 'date':
					case 'datetime':
					default:
						$sql_cell_definition .= ' '.strtoupper($cell_definition->type);
						break;
				}

				// NOT NULL 制約
				if( $cell_definition->not_null ){
					$sql_cell_definition .= ' NOT NULL';
				}

				// UNIQUE 制約
				if( $cell_definition->unique ){
					$sql_cell_definition .= ' UNIQUE';
				}

				array_push($ary_table_cells, $sql_cell_definition);
			}
			$sql_create_db .= implode($ary_table_cells, ',');
			$sql_create_db .= ');';
			// var_dump($sql_create_db);
			$result = @$this->exdb->pdo()->query($sql_create_db);
			// var_dump($result);
			if( !$result ){
				array_push($error, '[ERROR] Failed to create table "'.$this->exdb->get_physical_table_name($table_definition_row->table_name).'"');
			}
		}
		// var_dump($error);
		$rtn = true;
		if(count($error)){$rtn = false;}
		return $rtn;
	}

}
