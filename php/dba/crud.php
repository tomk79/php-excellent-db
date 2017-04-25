<?php
/**
 * excellent-db: DBA/CRUD
 */
namespace excellent_db;

/**
 * dba/crud.php
 */
class dba_crud{

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

}
