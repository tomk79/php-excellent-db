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

	/** Twig Instance */
	private $twig;

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


		// Twig テンプレートエンジンを準備
		$loader = new \Twig_Loader_Filesystem(__DIR__.'/../templates/');
		$this->twig = new \Twig_Environment($loader, array(
			// 'cache' => $this->exdb->conf()->path_cache_dir.'/twig_cache/',
			'debug' => true,
		));
		$this->twig->addExtension(new \Twig_Extension_Debug());

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

		$rtn = '';

		if( !strlen($this->options['table']) ){
			// --------------------
			// 対象のテーブルが選択されていない
			echo $this->page_table_list();
			return null;
		}

		if( $this->options['method'] == 'POST' ){
			// --------------------------------------
			// データベース操作系リクエスト

		}elseif( $this->options['method'] == 'GET' ){
			// --------------------------------------
			// 画面表示系リクエスト

			if( !strlen($this->options['id']) ){
				// ID無指定の場合、一覧情報を返す
				echo $this->page_list($this->options['table']);
				return null;
			}else{
				// ID指定がある場合、詳細情報1件を返す
			}

		}


		// 描画
		$rtn = $this->wrap_theme('<p>Unknown method</p>');
		echo $rtn;

		return null;
	}


	/**
	 * テーブル選択画面を描画
	 * @return String HTML Source Code
	 */
	private function page_table_list(){
		$table_defs_all = $this->exdb->get_table_definition_all();
		// var_dump($table_defs_all);
		$table_list = array();
		foreach( $table_defs_all->tables as $table_def ){
			// var_dump($table_def);
			$table = array();
			$table['label'] = $table_def->table_name;
			$table['table_name'] = $table_def->table_name;
			$table['href'] = $_SERVER['SCRIPT_NAME'].'/'.$table_def->table_name.'/';
			array_push($table_list, $table);
		}
		$rtn = $this->twig->render(
			'form_table_list.html',
			array(
				'table_list'=>$table_list,
			)
		);
		// var_dump($table_list);
		$rtn = $this->wrap_theme($rtn);
		return $rtn;
	} // page_table_list()


	/**
	 * 行の一覧画面を描画
	 * @return String HTML Source Code
	 */
	private function page_list( $table_name ){
		// var_dump($table_name);
		$list = $this->exdb->select($table_name, array(), $this->query_options);
		$count = $this->exdb->count($table_name, array());

		// var_dump($this->table_definition->system_columns);
		if( count($this->table_definition->system_columns->password) ){
			foreach( $list as $key=>$val ){
				foreach($this->table_definition->system_columns->password as $column_name){
					unset($list[$key][$column_name]);
				}
			}
		}
		$original_list = $list;
		$list = array();
		foreach( $original_list as $row ){
			$tmp_row = array();
			$tmp_row['label'] = $row[$this->table_definition->system_columns->id->column_name];
			$tmp_row['href'] = $_SERVER['SCRIPT_NAME'].'/'.$table_name.'/'.$row['user_id'].'/';
			$tmp_row['val'] = $row;
			array_push($list, $tmp_row);
		}

		$rtn = $this->twig->render(
			'form_list.html',
			array(
				'count'=>$count,
				'list'=>$list,
			)
		);
		// var_dump($table_list);
		$rtn = $this->wrap_theme($rtn);
		return $rtn;
	} // page_list()


	/**
	 * HTMLのテーマでラップする
	 * @param  String $html_content コンテンツエリアのHTMLソース
	 * @return String               テーマで包まれたHTMLソース
	 */
	private function wrap_theme($html_content){
		$rtn = $this->twig->render(
			'form_theme.html',
			array(
				'_SERVER'=>$_SERVER,
				'content'=>$html_content,
			)
		);
		return $rtn;
	}

}
