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
	 * @param array $config Database Config
	 * @param string $path_table_definition_file Path to Table Definition File(`*.xlsx`)
	 */
	public function __construct( $config, $path_table_definition_file ){
		$this->config = $config;
		$this->path_table_definition_file = $path_table_definition_file;
	}

}
