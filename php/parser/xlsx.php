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

			$sheetData = $this->parse_sheet( $objSheet );
			@$definition->tables->{$sheetData->table_name} = $sheetData;

		}

		return $definition;
	}


	/**
	 * シートを解析する
	 * @param  object $objSheet PHPExcel Sheet Instance
	 * @return object           Parsed sheet data
	 */
	private function parse_sheet( $objSheet ){
		$parsed = json_decode('{}');

		$table_name = $objSheet->getCell('B1')->getCalculatedValue();

		@$parsed->sheet_label = $objSheet->getTitle();
		@$parsed->table_name = $table_name;
		@$parsed->table_definition = json_decode('{}');

		// --------------------
		// 定義列を読み取り
		$col_define = array();
		$col = 'A';
		$skip_count = 0;
		while(1){
			$def_key = $objSheet->getCell($col.'3')->getCalculatedValue(); // TODO: 3行目を決め打ちしているが、フレキシブルにしたい。
			if(!strlen($def_key)){
				$skip_count ++;
				$col ++;
				if( $skip_count > 20){
					break;
				}
				continue;
			}
			$skip_count = 0;

			$col_define[$def_key] = array(
				'key'=>trim($def_key),
				'col'=>$col,
				// 'name'=>$def_name,
			);

			if( @strlen($mergeInfo[$col]) ){
				$mergeStartCol = $mergeInfo[$col];
				while( strcmp($mergeStartCol, $col) ){
					$col ++;
				}
			}else{
				$col ++;
			}
		}

		// var_dump($col_define);

		// --------------------
		// テーブル定義を読み取り
		$row_number = 4;
		while( 1 ){
			$col = json_decode('{}');
			foreach($col_define as $col_def_row){
				$col->{$col_def_row['key']} = $objSheet->getCell($col_def_row['col'].$row_number)->getCalculatedValue();
			}
			if(!@strlen($col->column_name)){
				break;
			}
			@$parsed->table_definition->{$col->column_name} = $col;
			$row_number ++;
			continue;
		}

		// --------------------
		// テーブル定義を整理
		// システムカラム情報を抽出
		$parsed->system_columns = json_decode(json_encode(array(
			'id'=>null,
			'create_date'=>null,
			'update_date'=>null,
			'delete_date'=>null,
			'delete_flg'=>null,
			'password'=>array(),
		)));
		foreach( $parsed->table_definition as $column_definition ){
			if( $column_definition->type == 'auto_id' || $column_definition->type == 'auto_increment' ){
				$parsed->system_columns->id = json_decode(json_encode(array(
					'type'=>$column_definition->type,
					'column_name'=>$column_definition->column_name,
				)));
			}else if( $column_definition->type == 'create_date' ){
				$parsed->system_columns->create_date = $column_definition->column_name;
			}elseif( $column_definition->type == 'update_date' ){
				$parsed->system_columns->update_date = $column_definition->column_name;
			}elseif( $column_definition->type == 'delete_date' ){
				$parsed->system_columns->delete_date = $column_definition->column_name;
			}elseif( $column_definition->type == 'delete_flg' ){
				$parsed->system_columns->delete_flg = $column_definition->column_name;
			}elseif( $column_definition->type == 'password' ){
				array_push($parsed->system_columns->password, $column_definition->column_name);
			}
			// var_dump($column_definition);
		}

		return $parsed;
	}

}
