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
		if( @!is_array($this->options['post_params']) ){
			$this->options['post_params'] = $_POST;
		}

		// table name
		if( @!strlen($this->options['table']) ){
			$this->options['table'] = $tmp_path_info[1];
		}

		// resource id
		if( @!strlen($this->options['id']) ){
			$this->options['id'] = $tmp_path_info[2];
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

		if( strlen($this->options['table']) ){
			$table_definition = $this->exdb->get_table_definition($this->options['table']);
		}

		$rtn = array();
		$rtn['result'] = false;

		if( $this->options['method'] == 'POST' ){
			// --------------------------------------
			// 投稿系リクエスト

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
				$rtn['list'] = $this->exdb->select($this->options['table'], $this->options['get_params']);
				$rtn['result'] = true;
				echo json_encode( $rtn );
				return null;
			}else{
				// ID指定がある場合、詳細情報1件を返す
				$where = $this->options['get_params'];
				$where[$table_definition->table_definition->system_columns->id->column_name] = $this->options['id'];
				$row = $this->exdb->select($this->options['table'], $where);
				$rtn['row'] = @$row[0];
				$rtn['result'] = true;
				echo json_encode( $rtn );
				return null;
			}

		}elseif( $this->options['method'] == 'PUT' ){
			// --------------------------------------
			// 更新系リクエスト

		}elseif( $this->options['method'] == 'DELETE' ){
			// --------------------------------------
			// 削除系リクエスト

		}

		$rtn['options'] = $this->options;
		$rtn['_SERVER'] = $_SERVER;
		$rtn['_GET'] = $_GET;
		$rtn['_POST'] = $_POST;
		$rtn['error'] = 'Unknown method';

		echo json_encode( $rtn );
		return null;
	}

}