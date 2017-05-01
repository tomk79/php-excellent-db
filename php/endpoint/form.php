<?php
/**
 * excellent-db: Endpoint/Form
 */
namespace excellent_db;

/**
 * endpoint/form.php
 */
class endpoint_form{

	/** ExcellentDb Object */
	private $exdb;

	/** Options */
	private $options;

	/** Query Options */
	private $query_options;

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
		@header('text/html; charset=UTF-8');

		if( strlen($this->options['table']) ){
			$table_definition = $this->exdb->get_table_definition($this->options['table']);
		}

		$rtn = '';

		if( $this->options['method'] == 'POST' ){
			// --------------------------------------
			// データベース操作系リクエスト

		}elseif( $this->options['method'] == 'GET' ){
			// --------------------------------------
			// 画面表示系リクエスト

		}

		$rtn = '<html><body>Unknown method</body></html>';
		echo $rtn;
		return null;
	}

}
