<?php
/**
 * excellent-db: XLSX Database Definition Parser
 */
namespace excellent_db;

/**
 * parser/xlsx.php
 */
class parser_xlsx{

	/** ExcellentDb Object */
	private $exdb;

	/**
	 * constructor
	 *
	 * @param object $exdb ExcellentDb Object
	 */
	public function __construct( $exdb ){
		$this->exdb = $exdb;
		return;
	}

	/**
	 * 解析する。
	 * @return object Table Definition Info.
	 */
	public function parse(){
		$definition = json_decode('{}');
		return $definition;
	}

}
