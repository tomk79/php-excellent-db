<?php
/**
 * excellent-db
 */
namespace excellent_db;

/**
 * create.php
 */
class create{

	/** tomk79/filesystem Instance */
	private $fs;

	/** cache manager Instance */
	private $caches;

	/** PDO Instance */
	private $pdo;

	/** Database Config */
	private $config;

	/** Table Definition */
	private $table_definition;

	/** CRUD Operator */
	private $crud;

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
		$env_error = $this->validate_env();
		if( $env_error ){
			trigger_error('[ExcellentDb: Setup Error] '.$env_error);
			return;
		}

		// Generate CRUD Operator
		$this->crud = new dba_crud( $this );

		$this->reload_definition_data();
		return;
	}

	/**
	 * 環境情報(設定を含む)を検証する
	 * @return string Error message.
	 */
	private function validate_env(){
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
	 * @return object Table Definition.
	 */
	public function get_table_definition( $table_name ){
		return $this->table_definition->tables->{$table_name};
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
	 * INSERT
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
	 * SELECT
	 */
	public function select($tbl, $where, $options = array()){
		return $this->crud->select($tbl, $where);
	}

	/**
	 * UPDATE
	 */
	public function update($tbl, $where, $data){
		return $this->crud->update($tbl, $where, $data);
	}

	/**
	 * DELETE (Logical Deletion)
	 */
	public function delete($tbl, $where){
		return $this->crud->delete($tbl, $where);
	}

	/**
	 * DELETE (Physical Deletion)
	 */
	public function physical_delete($tbl, $where){
		return $this->crud->physical_delete($tbl, $where);
	}

}
