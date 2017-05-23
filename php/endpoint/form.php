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

	/** ROW Data */
	private $row_data;

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
			if( preg_match('/^\:/', $key) ){
				$tmp_key = preg_replace('/^\:/', '', $key);
				$this->query_options[$tmp_key] = $val;
				unset($this->options['get_params'][$key]);
			}
		}

		if( @!is_array($this->options['post_params']) ){
			$this->options['post_params'] = $_POST;
		}
		foreach($this->options['post_params'] as $key=>$val){
			if( preg_match('/^\:/', $key) ){
				$tmp_key = preg_replace('/^\:/', '', $key);
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

		// action name
		if( @!strlen($this->options['action']) ){
			$this->options['action'] = @$tmp_path_info[3];
		}

		if( strlen($this->options['table']) ){
			// テーブル名が指定されている場合、テーブル定義を取得する
			$this->table_definition = $this->exdb->get_table_definition($this->options['table']);
			if( $this->table_definition && strlen($this->options['id']) ){
				// さらに、行のIDが指定されている場合、
				// 行データをSELECTしておく。
				$list = $this->exdb->select(
					$this->options['table'],
					array(
						$this->table_definition->key_column=>$this->options['id']
					),
					array()
				);
				$this->row_data = @$list[0];
				if( count($this->table_definition->system_columns->password) ){
					foreach($this->table_definition->system_columns->password as $column_name){
						unset($this->row_data[$column_name]);
					}
				}
			}
		}


		// Twig テンプレートエンジンを準備
		$loader = new \Twig_Loader_Filesystem(__DIR__.'/../../templates/');
		$this->twig = new \Twig_Environment($loader, array(
			// 'cache' => $this->exdb->conf()->path_cache_dir.'/twig_cache/',
			'debug' => true,
		));
		$this->twig->addExtension(new \Twig_Extension_Debug());

		return;
	}

	/**
	 * Getting Options
	 */
	public function get_options(){
		return $this->options;
	}

	/**
	 * Getting Query Options
	 */
	public function get_query_options(){
		return $this->query_options;
	}

	/**
	 * Getting Current Table Definition
	 */
	public function get_current_table_definition(){
		return ($this->table_definition ? $this->table_definition : false);
	}

	/**
	 * Getting Current ROW Data
	 */
	public function get_current_row_data(){
		return ($this->row_data ? $this->row_data : false);
	}

	/**
	 * Execute Automatic form
	 *
	 * @return null This method returns no value.
	 */
	public function automatic_form(){
		@header('text/html; charset=UTF-8');

		$rtn = '';

		if( !strlen($this->options['table']) ){
			// --------------------
			// 対象のテーブルが選択されていない
			echo $this->page_table_list();
			return null;
		}

		$table_definition = $this->get_current_table_definition();
		// var_dump($table_definition);
		if( !$table_definition ){
			@header("HTTP/1.0 404 Not Found");
			$rtn = $this->page_fatal_error('Table NOT Exists.');
			echo $rtn;
			return null;
		}

		if( !strlen($this->options['id']) ){
			// ID無指定の場合、一覧情報を返す
			echo $this->page_list($this->options['table']);
			return null;
		}elseif( $this->options['id'] == ':create' ){
			// IDの代わりに文字列 `:create` が指定されたら、新規作成画面を返す
			echo $this->page_edit($this->options['table']);
			return null;
		}else{
			// ID指定がある場合、詳細情報1件を返す
			$row_data = $this->get_current_row_data();
			if( !$row_data && !($this->options['action'] == 'delete' && $this->query_options['action'] == 'done') ){
				@header("HTTP/1.0 404 Not Found");
				$rtn = $this->page_fatal_error('ID NOT Exists.');
				echo $rtn;
				return null;
			}

			if( $this->options['action'] == 'delete' ){
				echo $this->page_delete($this->options['table'], $this->options['id']);
			}elseif( $this->options['action'] == 'edit' ){
				echo $this->page_edit($this->options['table'], $this->options['id']);
			}else{
				echo $this->page_detail($this->options['table'], $this->options['id']);
			}
			return null;
		}



		// エラー画面
		$rtn = $this->page_fatal_error('Unknown method');
		echo $rtn;

		return null;
	} // automatic_form();


	/**
	 * Execute Automatic Auth form
	 *
	 * @param  string $table_name テーブル名
	 * @param  array $inquiries 照会するカラム名
	 * @return boolean Always `true`.
	 */
	public function auth($table_name, $inquiries){
		$options = $this->get_options();
		$data = $options['post_params'];

		if(@$this->query_options['action'] == 'login'){
			// ログインを試みる
			$result = $this->exdb->user()->login( $table_name, $inquiries, $data );
		}


		$is_login = $this->exdb->user()->is_login($table_name);
		if( $is_login ){
			// ログイン処理済みなら終了
			return true;
		}

		$table_definition = $this->exdb->get_table_definition($table_name);
		// var_dump($table_definition->columns);

		$rtn = '';
		foreach( $inquiries as $column_name ){
			$column_definition = $table_definition->columns->{$column_name};
			$rtn .= $this->render(
				'form_elms/default/edit.html',
				array(
					'value'=>'',
					'error'=>'',
					'def'=>@$column_definition,
				)
			);
		}

		$rtn = $this->render(
			'form_login.html',
			array(
				'is_error'=>(@$this->query_options['action'] == 'login'),
				'content'=>$rtn,
			)
		);
		// var_dump($table_list);
		$rtn = $this->wrap_theme($rtn);
		echo $rtn;
		exit();
		return false;

	} // auth();

	/**
	 * ログアウトする
	 * @param  string $table_name テーブル名
	 * @return boolean Always `true`.
	 */
	public function logout($table_name){
		$result = $this->exdb->user()->logout( $table_name );
		return true;
	} // logout()


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
			$table['label'] = $table_def->label;
			$table['name'] = $table_def->name;
			$table['href'] = $this->generate_url($table_def->name);
			array_push($table_list, $table);
		}
		$rtn = $this->render(
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
	 * 一覧画面を描画
	 * @param  string $table_name テーブル名
	 * @return String HTML Source Code
	 */
	private function page_list( $table_name ){
		// var_dump($table_name);
		$max_count = $this->exdb->count($table_name, array());
		$this->query_options['limit'] = @intval($this->query_options['limit']);
		if( $this->query_options['limit'] <= 0 ){
			$this->query_options['limit'] = 10;
		}
		$page = intval(@$this->query_options['page']);
		if( $page == 0 ){ $page = 1; }
		$max_page = intval($max_count/$this->query_options['limit'])+1;
		if( $page < 1 || $page > $max_page ){
			@header("HTTP/1.0 404 Not Found");
			$rtn = $this->page_fatal_error('No Items.');
			echo $rtn;
			return null;
		}

		$list = $this->exdb->select($table_name, array(), $this->query_options);

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
			$tmp_row['label'] = $row[$this->table_definition->key_column];
			$tmp_row['href'] = $this->generate_url($table_name, $row[$this->table_definition->key_column]);
			$tmp_row['val'] = $row;
			array_push($list, $tmp_row);
		}

		$nav_prev = array(
			'href'=>$this->generate_url($table_name, null, null).'?:page='.($page -1),
		);
		if( $page <= 1 ){
			$nav_prev = null;
		}
		$nav_next = array(
			'href'=>$this->generate_url($table_name, null, null).'?:page='.($page +1),
		);
		if( $page >= $max_page ){
			$nav_next = null;
		}

		$rtn = $this->render(
			'form_list.html',
			array(
				'table_label'=>$this->table_definition->label,
				'count'=>$max_count,
				'list'=>$list,
				'href_create'=>$this->generate_url($table_name, null, 'create'),
				'page'=>$page,
				'max_page'=>$max_page,
				'prev'=>$nav_prev,
				'next'=>$nav_next,
			)
		);
		// var_dump($table_list);
		$rtn = $this->wrap_theme($rtn);
		return $rtn;
	} // page_list()


	/**
	 * 詳細画面を描画
	 * @param  string $table_name テーブル名
	 * @param  string $row_id 表示する行のID値
	 * @return String HTML Source Code
	 */
	private function page_detail( $table_name, $row_id ){
		// var_dump($table_name);
		$list = $this->exdb->select($table_name, array($this->table_definition->key_column=>$row_id), $this->query_options);

		// var_dump($this->table_definition->system_columns);
		if( count($this->table_definition->system_columns->password) ){
			foreach( $list as $key=>$val ){
				foreach($this->table_definition->system_columns->password as $column_name){
					unset($list[$key][$column_name]);
				}
			}
		}
		$rtn = '';
		foreach( $this->table_definition->columns as $column_definition ){
			// var_dump($column_definition);
			$rtn .= $this->render(
				'form_elms/default/detail.html',
				array(
					'value'=>@$list[0][$column_definition->column_name],
					'def'=>@$column_definition,
				)
			);
		}

		$rtn = $this->render(
			'form_detail.html',
			array(
				'href_edit'=>$this->generate_url($table_name, $row_id, 'edit'),
				'href_delete'=>$this->generate_url($table_name, $row_id, 'delete'),
				'href_list'=>$this->generate_url($table_name),
				'content'=>@$rtn,
			)
		);

		// $rtn = '<form>'.$rtn.'</form>';
		$rtn = $this->wrap_theme($rtn);
		return $rtn;
	} // page_detail()


	/**
	 * 編集画面を描画
	 * @param  string $table_name テーブル名
	 * @param  string $row_id 表示する行のID値
	 * @return String HTML Source Code
	 */
	private function page_edit( $table_name, $row_id = null ){
		$page_edit = new endpoint_form_edit($this->exdb, $this, $table_name, $row_id);
		return $page_edit->execute();
	} // page_edit()


	/**
	 * 削除画面を描画
	 * @param  string $table_name テーブル名
	 * @param  string $row_id 表示する行のID値
	 * @return String HTML Source Code
	 */
	private function page_delete( $table_name, $row_id ){
		$page_delete = new endpoint_form_delete($this->exdb, $this, $table_name, $row_id);
		return $page_delete->execute();
	} // page_delete()

	/**
	 * 致命的なエラー画面
	 * @param  array $errors エラーメッセージ
	 * @return String HTML Source Code
	 */
	public function page_fatal_error($errors = ''){
		$rtn = '';
		$rtn .= '<p>'.htmlspecialchars($errors).'</p>';
		$rtn = $this->wrap_theme($rtn);
		return $rtn;
	}

	/**
	 * URLを生成する
	 * @param  string $table_name テーブル名
	 * @param  string $row_id 表示する行のID値
	 * @param  string $action actionスイッチ
	 * @return string URL文字列
	 */
	public function generate_url($table_name = null, $row_id = null, $action = null){
		if( strlen( $table_name ) && !strlen( $row_id ) && $action == 'create' ){
			return @$_SERVER['SCRIPT_NAME'].'/'.$table_name.'/:create/';
		}
		if( strlen( $table_name ) && strlen( $row_id ) && strlen( $action ) ){
			return @$_SERVER['SCRIPT_NAME'].'/'.$table_name.'/'.$row_id.'/'.$action.'/';
		}
		if( strlen( $table_name ) && strlen( $row_id ) ){
			return @$_SERVER['SCRIPT_NAME'].'/'.$table_name.'/'.$row_id.'/';
		}
		if( strlen( $table_name ) ){
			return @$_SERVER['SCRIPT_NAME'].'/'.$table_name.'/';
		}
		return @$_SERVER['SCRIPT_NAME'].'/';
	}

	/**
	 * テンプレートを描画する
	 * @param  string $template Twigテンプレートコード
	 * @param  array $data バインドデータ
	 * @return string バインドされたHTMLソース
	 */
	public function render($template, $data){
		$rtn = $this->twig->render($template, $data);
		return $rtn;
	}

	/**
	 * HTMLのテーマでラップする
	 * @param  String $html_content コンテンツエリアのHTMLソース
	 * @return String               テーマで包まれたHTMLソース
	 */
	public function wrap_theme($html_content){
		$rtn = $this->render(
			'form_theme.html',
			array(
				'_SERVER'=>$_SERVER,
				'content'=>$html_content,
			)
		);
		return $rtn;
	}

}
