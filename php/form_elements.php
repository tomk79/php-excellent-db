<?php
/**
 * excellent-db: Form Elements Manager
 */
namespace excellent_db;

/**
 * form_elements.php
 */
class form_elements{

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
	 * フォーム要素のタイプ別の情報を取得する
	 */
	public function get_type_info($type){
		$path_base_dir = __DIR__.'/../templates/';
		if( !is_dir( $path_base_dir.'form_elms/'.urlencode($type).'/' ) ){
			$type = 'text';
		}
		$rtn = array();
		$rtn['type'] = $type;
		$rtn['templates'] = array();
		$rtn['templates']['input'] = 'form_elms/'.urlencode($type).'/input.html';
		$rtn['templates']['preview'] = 'form_elms/'.urlencode($type).'/preview.html';
		$rtn['validate'] = include($path_base_dir.'form_elms/'.urlencode($type).'/validate.php');
		return $rtn;
	}

}
