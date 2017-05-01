<?php
/**
 * excellent-db: Endpoint/REST API
 */
namespace excellent_db;

/**
 * endpoint/rest.php
 */
class endpoint_rest{

	/** ExcellentDb Object */
	private $exdb;

	/** Options */
	private $options;

	/** Query Options */
	private $query_options;

	/** Table Definition */
	private $table_definition;

	/**
	 * constructor
	 *
	 * @param object $exdb ExcellentDb Object
	 * @param array $options Options
	 */
	public function __construct( $exdb, $options ){
		$this->exdb = $exdb;
		$this->options = $options;

		$tmp_path_info = @$_SERVER['PATH_INFO'];
		$tmp_path_info = explode('/', $tmp_path_info);

		// オプション値の初期化
		if( @is_null($this->options) ){
			$this->options = array();
		}

		// method
		if( @!strlen($this->options['method']) ){
			$this->options['method'] = $_SERVER['REQUEST_METHOD'];
		}
		$this->options['method'] = strtoupper($this->options['method']);

		// params

		if( @!is_array($this->options['get_params']) ){
			$this->options['get_params'] = $_GET;
		}
		foreach($this->options['get_params'] as $key=>$val){
			if( preg_match('/^\//', $key) ){
				$tmp_key = preg_replace('/^\//', '', $key);
				$this->query_options[$tmp_key] = $val;
				unset($this->options['get_params'][$key]);
			}
		}

		if( @!is_array($this->options['post_params']) ){
			$this->options['post_params'] = $_POST;
		}
		foreach($this->options['post_params'] as $key=>$val){
			if( preg_match('/^\//', $key) ){
				$tmp_key = preg_replace('/^\//', '', $key);
				$this->query_options[$tmp_key] = $val;
				unset($this->options['post_params'][$key]);
			}
		}

		// table name
		if( @!strlen($this->options['table']) ){
			$this->options['table'] = @$tmp_path_info[1];
		}

		// resource id
		if( @!strlen($this->options['id']) ){
			$this->options['id'] = @$tmp_path_info[2];
		}

		if( strlen($this->options['table']) ){
			$this->table_definition = $this->exdb->get_table_definition($this->options['table']);
		}

		return;
	}

	/**
	 * Execute
	 *
	 * @param object $exdb ExcellentDb Object
	 * @return null This method returns no value.
	 */
	public function execute(){
		@header('text/json; charset=UTF-8');

		$rtn = array();
		$rtn['result'] = false;
		// $rtn['options'] = $this->options;
		// $rtn['_SERVER'] = $_SERVER;
		// $rtn['_GET'] = $_GET;
		// $rtn['_POST'] = $_POST;

		if( $this->options['method'] == 'POST' ){
			// --------------------------------------
			// 投稿系リクエスト
			if( !strlen($this->options['table']) ){
				// 対象のテーブルが不明な場合はエラー
				$rtn['error'] = 'Table name not set.';
				echo json_encode( $rtn );
				return null;
			}
			if( strlen($this->options['id']) ){
				// 対象のテーブルが指定された場合はエラー
				$rtn['error'] = 'Resource ID was set.';
				echo json_encode( $rtn );
				return null;
			}

			$params = $this->options['post_params'];
			$result = $this->exdb->insert($this->options['table'], $params);
			$rtn['result'] = $result;
			$last_insert_info = $this->exdb->get_last_insert_info();
			$rtn['given_id'] = $last_insert_info->value;
			echo json_encode( $rtn );
			return null;

		}elseif( $this->options['method'] == 'GET' ){
			// --------------------------------------
			// 取得系リクエスト
			if( !strlen($this->options['table']) ){
				// 対象のテーブルが不明な場合はエラー
				$rtn['error'] = 'Table name not set.';
				echo json_encode( $rtn );
				return null;
			}

			if( !strlen($this->options['id']) ){
				// ID無指定の場合、一覧情報を返す
				$rtn['list'] = $this->exdb->select($this->options['table'], $this->options['get_params'], $this->query_options);
				$rtn['count'] = $this->exdb->count($this->options['table'], $this->options['get_params']);
				$rtn['result'] = true;
				if( count($this->table_definition->system_columns->password) ){
					foreach( $rtn['list'] as $key=>$val ){
						foreach($this->table_definition->system_columns->password as $column_name){
							unset($rtn['list'][$key][$column_name]);
						}
					}
				}
				echo json_encode( $rtn );
				return null;
			}else{
				// ID指定がある場合、詳細情報1件を返す
				$where = $this->options['get_params'];
				// $rtn['table_definition'] = $this->table_definition;
				$where[$this->table_definition->system_columns->id->column_name] = $this->options['id'];
				$row = $this->exdb->select($this->options['table'], $where);
				$rtn['row'] = @$row[0];
				foreach($this->table_definition->system_columns->password as $column_name){
					unset($rtn['row'][$column_name]);
				}
				$rtn['result'] = true;
				echo json_encode( $rtn );
				return null;
			}

		}elseif( $this->options['method'] == 'PUT' ){
			// --------------------------------------
			// 更新系リクエスト
			if( !strlen($this->options['table']) ){
				// 対象のテーブルが不明な場合はエラー
				$rtn['error'] = 'Table name not set.';
				echo json_encode( $rtn );
				return null;
			}
			if( !strlen($this->options['id']) ){
				// 対象のテーブルが不明な場合はエラー
				$rtn['error'] = 'Resource ID not set.';
				echo json_encode( $rtn );
				return null;
			}

			$params = $this->options['post_params'];
			$result = $this->exdb->update(
				$this->options['table'],
				array(
					$this->table_definition->system_columns->id->column_name => $this->options['id'],
				),
				$params
			);
			$rtn['result'] = ($result ? true : false);
			$rtn['affected_rows'] = $result;
			echo json_encode( $rtn );
			return null;

		}elseif( $this->options['method'] == 'DELETE' ){
			// --------------------------------------
			// 削除系リクエスト
			if( !strlen($this->options['table']) ){
				// 対象のテーブルが不明な場合はエラー
				$rtn['error'] = 'Table name not set.';
				echo json_encode( $rtn );
				return null;
			}
			if( !strlen($this->options['id']) ){
				// 対象のテーブルが不明な場合はエラー
				$rtn['error'] = 'Resource ID not set.';
				echo json_encode( $rtn );
				return null;
			}

			$result = $this->exdb->delete(
				$this->options['table'],
				array(
					$this->table_definition->system_columns->id->column_name => $this->options['id'],
				)
			);
			$rtn['result'] = ($result ? true : false);
			$rtn['affected_rows'] = $result;
			echo json_encode( $rtn );
			return null;

		}

		$rtn['error'] = 'Unknown method';

		echo json_encode( $rtn );
		return null;
	}

}
