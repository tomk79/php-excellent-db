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
	 * 解析する
	 * @param string $path_definition_file Path to Table Definition File
	 * @return object Table Definition Info.
	 */
	public function parse($path_definition_file){
		$definition = json_decode('{}');

		$objPHPExcel = \PHPExcel_IOFactory::load($path_definition_file);

		for($sheetIndex = 0; $sheetIndex<$objPHPExcel->getSheetCount(); $sheetIndex ++){
			$objPHPExcel->setActiveSheetIndex($sheetIndex);
			$objSheet = $objPHPExcel->getActiveSheet();

			$table_name = $objSheet->getCell('B1')->getCalculatedValue();
			@$definition->tables->{$table_name} = json_decode('{}');
			@$definition->tables->{$table_name}->sheet_label = $objSheet->getTitle();
		}

		return $definition;
	}

}
