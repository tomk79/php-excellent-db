<?php
/**
 * excellent-db
 */
namespace excellent_db;

/**
 * caches.php
 */
class caches{

	/** Excellent Db Instance */
	private $exdb;

	/**
	 * constructor
	 *
	 * @param object $exdb Excellent Db Instance
	 * @param object $config Database Config
	 */
	public function __construct( $exdb ){
		$this->exdb = $exdb;
		return;
	}

	/**
	 * キャッシュからデータを読み込んで返す。
	 * @return object 読み込んだデータ。失敗時 `false` を返します。
	 */
	public function load_cached_contents(){
		$caches = json_decode('{}');
		$path_json = $this->exdb->conf()->path_cache_dir.'/table_definition.json';
		$path_xlsx = $this->exdb->conf()->path_definition_file;
		if( $this->exdb->fs()->is_newer_a_than_b( $path_xlsx, $path_json ) ){
			return false;
		}

		if(!is_file($path_json)){
			return false;
		}
		if(!is_readable($path_json)){
			return false;
		}
		$json = file_get_contents($path_json);
		$caches->table_definition = json_decode($json);
		return $caches;
	}

	/**
	 * キャッシュを消去する。
	 * @return boolean キャッシュ消去の成否。
	 */
	public function clear(){
		$result = false;
		$path_json = $this->exdb->conf()->path_cache_dir.'/table_definition.json';
		if(is_file($path_json)){
			unlink($path_json);
		}
		$result = !is_file($path_json);
		return $result;
	}

}
