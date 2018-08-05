<?php
/**
 * excellent-db
 */
namespace excellent_db;

/**
 * create.php
 */
class create{

	/**
	 * tomk79/filesystem Instance
	 * @ignore
	 */
	private $fs;

	/**
	 * PDO Instance
	 * @ignore
	 */
	private $pdo;

	/**
	 * Database Config
	 * @ignore
	 */
	private $config;

	/**
	 * Table Definition
	 * @ignore
	 */
	private $table_definition;

	/**
	 * Login User Manager Instance
	 * @ignore
	 */
	private $user;

	/**
	 * CRUD Operator Instance
	 * @ignore
	 */
	private $crud;

	/**
	 * Session Manager Instance
	 * @ignore
	 */
	private $session;

	/**
	 * Form Elements Manager
	 */
	private $form_elements;

	/**
	 * Validator Instance
	 * @ignore
	 */
	private $validator;

	/**
	 * cache manager Instance
	 * @ignore
	 */
	private $caches;

	/**
	 * constructor
	 *
	 * @param object $pdo PDO Instance
	 * @param object $config Database Config
	 */
	public function __construct( $pdo, $config ){
		$this->pdo = $pdo;
		$this->config = json_decode(json_encode($config));

		// filesystem utilitiy
		$this->fs = new \tomk79\filesystem();

		// cache manager
		$this->caches = new caches($this);

		// 環境情報をチェック
		$env_error = $this->check_env();
		if( $env_error ){
			trigger_error('[ExcellentDb: Setup Error] '.$env_error);
			return;
		}

		// Generate CRUD Operator
		$this->crud = new dba_crud( $this );

		// Generate Session Manager
		$this->session = new dba_session( $this );

		// Form Elements Manager
		$this->form_elements = new form_elements( $this );

		// Generate Validator
		$this->validator = new validator( $this );

		// Generate Login User Manager
		$this->user = new user( $this );

		$this->reload_definition_data();
		return;
	}

	/**
	 * 環境情報(設定を含む)を検証する
	 * @ignore
	 * @return string Error message.
	 */
	private function check_env(){
		if( !is_file($this->config->path_definition_file) ){
			return 'Table definition file is NOT a file (or NOT exists).';
		}
		if( !is_readable($this->config->path_definition_file) ){
			return 'Table definition file is NOT readable.';
		}

		if( !is_dir($this->config->path_cache_dir) ){
			return 'path_cache_dir is NOT a directory (or NOT exists).';
		}
		if( !is_readable($this->config->path_definition_file) ){
			return 'path_cache_dir is NOT readable.';
		}
		if( !is_writable($this->config->path_definition_file) ){
			return 'path_cache_dir is NOT writable.';
		}
		return null;
	}

	/**
	 * 設定情報を取得する
	 * @return object Database Config
	 */
	public function conf(){
		return $this->config;
	}

	/**
	 * `$fs` を取得する
	 * @return object Filesystem Utilitiy Instance.
	 */
	public function fs(){
		return $this->fs;
	}

	/**
	 * `$pdo` を取得する
	 * @return object PDO Instance.
	 */
	public function pdo(){
		return $this->pdo;
	}

	/**
	 * `$session` を取得する
	 * @return object Session Manager Instance.
	 */
	public function session(){
		return $this->session;
	}

	/**
	 * `$user` を取得する
	 * @return object Login User Manager Instance.
	 */
	public function user(){
		return $this->user;
	}

	/**
	 * フォーム要素マネージャを取得
	 */
	public function form_elements(){
		return $this->form_elements;
	}

	/**
	 * パスワードをハッシュ値化する
	 * @param  String $password 平文のパスワード
	 * @return String           ハッシュ値化後のパスワード
	 */
	public function encrypt_password($password){
		return md5($password);
	}

	/**
	 * すべてのテーブル設計を取得する
	 * @return object Table Definition.
	 */
	public function get_table_definition_all(){
		return $this->table_definition;
	}

	/**
	 * テーブル設計を取得する
	 * @param  string $table_name テーブル名
	 * @return object Table Definition.
	 */
	public function get_table_definition( $table_name ){
		$tabel_definition = @$this->table_definition->tables->{$table_name};
		return ($tabel_definition ? $tabel_definition : false);
	}

	/**
	 * テーブル定義ファイルを解析する
	 * @param string $path_definition_file Path to Table Definition File (default to `$config->path_definition_file`)
	 * @return object Table Definition Info.
	 */
	public function parse_definition_file( $path_definition_file = null ){
		if(!strlen($path_definition_file)){
			$path_definition_file = $this->config->path_definition_file;
		}
		if( !is_file($path_definition_file) || !is_readable($path_definition_file) ){
			trigger_error('File NOT found, or NOT readable.');
			return false;
		}
		$parser = new parser_xlsx($this);
		$rtn = $parser->parse($path_definition_file);
		return $rtn;
	}

	/**
	 * 定義データをリロードする
	 * @return boolean 成否。
	 */
	public function reload_definition_data(){
		// キャッシュから読み込み
		$caches = $this->caches->load_cached_contents();

		if( $caches === false ){
			// テーブル定義ファイルの解析を実行
			$table_definition = $this->parse_definition_file();
			$this->fs->save_file(
				$this->config->path_cache_dir.'/table_definition.json',
				json_encode($table_definition, JSON_PRETTY_PRINT)
			);
			$caches = $this->caches->load_cached_contents();
		}
		if( $caches === false ){
			trigger_error('[ExcellentDb: Setup Error] Could NOT read data.');
			return;
		}
		$this->table_definition = $caches->table_definition;
		return true;
	}

	/**
	 * キャッシュを消去する
	 * @return boolean 成否。
	 */
	public function clearcache(){
		return $this->caches->clear();
	}

	/**
	 * データベーステーブルを初期化する
	 * @return boolean 成否。
	 */
	public function migrate_init_tables(){
		$migrate_init_tables = new migrate_init_tables($this);
		return $migrate_init_tables->init();
	}

	/**
	 * getting table prefix
	 * @param  string $table_name テーブル名
	 * @return String Table Prefix
	 */
	public function get_physical_table_name($table_name){
		$prefix = $this->conf()->prefix;
		if( strlen($prefix) ){
			$prefix = preg_replace('/\_*$/', '_', $prefix);
		}
		return $prefix.$table_name;
	}


	/**
	 * 編集可能なカラムか調べる
	 * @param  object  $column_definition カラム定義
	 * @return boolean                    編集可否
	 */
	public function is_editable_column( $column_definition ){
		if($column_definition->type == 'auto_id'){
			return false;
		}elseif($column_definition->type == 'auto_increment'){
			return false;
		}elseif($column_definition->type == 'create_date'){
			return false;
		}elseif($column_definition->type == 'update_date'){
			return false;
		}elseif($column_definition->type == 'delete_date'){
			return false;
		}elseif($column_definition->type == 'delete_flg'){
			return false;
		}
		return true;
	}

	/**
	 * INSERT文を発行する
	 * @param  string $tbl テーブル名
	 * @param  array $data 挿入するデータ
	 * @return boolean 実行結果の成否
	 */
	public function insert($tbl, $data){
		return $this->crud->insert($tbl, $data);
	}

	/**
	 * 最後に挿入したレコードを引くためのキー情報を取得する
	 * @return Array キー情報を格納する連想配列
	 */
	public function get_last_insert_info(){
		return $this->crud->get_last_insert_info();
	}

	/**
	 * 最後に挿入したレコードを取得する
	 * @return Array 行情報
	 */
	public function get_last_insert_row(){
		$last_insert_info = $this->crud->get_last_insert_info();
		// var_dump($last_insert_info->table_name, $last_insert_info->insert_data);
		$result = $this->crud->select( $last_insert_info->table_name, $last_insert_info->insert_data );
		// var_dump($result);
		if( !is_array($result) || !count($result) ){
			return false;
		}
		return $result[0];
	}

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
		return $this->crud->select($tbl, $where, $options);
	}

	/**
	 * SELECT文を発行し、該当件数を調べる
	 * @param  string $tbl テーブル名
	 * @param  array $where 抽出条件
	 * @return int 抽出されたレコード数
	 */
	public function count($tbl, $where){
		return $this->crud->count($tbl, $where);
	}

	/**
	 * UPDATE文を発行する
	 * @param  string $tbl テーブル名
	 * @param  array $where 抽出条件
	 * @param  array $data 更新するデータ
	 * @return int 変更が反映されたレコード数
	 */
	public function update($tbl, $where, $data){
		return $this->crud->update($tbl, $where, $data);
	}

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
		return $this->crud->delete($tbl, $where);
	}

	/**
	 * DELETE文を発行する (Physical Deletion)
	 * @param  string $tbl テーブル名
	 * @param  array $where 抽出条件
	 * @return int 変更が反映されたレコード数
	 */
	public function physical_delete($tbl, $where){
		return $this->crud->physical_delete($tbl, $where);
	}


	/**
	 * $validator
	 */
	public function validator(){
		return $this->validator;
	}

	/**
	 * データを検証する
	 * @param  string $table テーブル名
	 * @param  array $data 入力データ
	 * @return array エラー配列
	 */
	public function validate( $table, $data ){
		$errors = $this->validator->validate_table($table, $data);
		return $errors;
	}

	/**
	 * REST APIエンドポイントを取得
	 * @param  array $options オプション
	 * @return object [excellent_db\endpoint_rest](./excellent_db.endpoint_rest.html) のインスタンス
	 */
	public function get_rest($options = null){
		$api = new endpoint_rest( $this, $options );
		return $api;
	}

	/**
	 * フォームエンドポイントを取得
	 *
	 * @param  array $options オプション
	 * @return object [excellent_db\endpoint_form](./excellent_db.endpoint_form.html) のインスタンス
	 */
	public function get_form($options = null){
		$api = new endpoint_form( $this, $options );
		return $api;
	}

}
