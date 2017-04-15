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

	/** PDO Instance */
	private $pdo;

	/** Database Config */
	private $config;

	/**
	 * constructor
	 *
	 * @param object $pdo PDO Instance
	 * @param object $config Database Config
	 */
	public function __construct( $pdo, $config ){
		$this->pdo = $pdo;
		$this->config = json_decode(json_encode($config));

		$this->fs = new \tomk79\filesystem();

		// 環境情報をチェック
		$env_error = $this->validate_env();
		if( $env_error ){
			trigger_error('[ExcellentDb: Setup Error] '.$env_error);
			return;
		}

		// テーブル定義ファイルの解析を実行
		$table_definition = $this->parse_definition_file();
		$this->fs->save_file(
			$this->config->path_cache_dir.'/table_definition.json',
			json_encode($table_definition, JSON_PRETTY_PRINT)
		);
		return;
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

}
