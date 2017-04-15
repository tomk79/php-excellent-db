<?php
/**
 * excellent-db
 */
namespace excellent_db;

/**
 * create.php
 */
class create{

	/** Database Config */
	private $config;

	/** Path to table definition file */
	private $path_table_definition_file;

	/**
	 * constructor
	 *
	 * @param object $config Database Config
	 * @param string $path_table_definition_file Path to Table Definition File(`*.xlsx`)
	 */
	public function __construct( $config, $path_table_definition_file ){
		$this->config = json_decode(json_encode($config));
		$this->path_table_definition_file = $path_table_definition_file;

		// 環境情報をチェック
		$env_error = $this->validate_env();
		if( $env_error ){
			trigger_error('[ExcellentDb: Setup Error] '.$env_error);
			return;
		}
		return;
	}

	/**
	 * 環境情報(設定を含む)を検証する
	 * @return string Error message.
	 */
	private function validate_env(){
		if( !is_file($this->path_table_definition_file) ){
			return 'Table definition file is NOT a file (or NOT exists).';
		}
		if( !is_readable($this->path_table_definition_file) ){
			return 'Table definition file is NOT readable.';
		}

		if( !is_dir($this->config->path_cache_dir) ){
			return 'path_cache_dir is NOT a directory (or NOT exists).';
		}
		if( !is_readable($this->path_table_definition_file) ){
			return 'path_cache_dir is NOT readable.';
		}
		if( !is_writable($this->path_table_definition_file) ){
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
