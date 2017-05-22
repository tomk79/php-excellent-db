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
			@$definition->tables->{$sheetData->name} = $sheetData;

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

		@$parsed->sheet_label = $objSheet->getTitle();
		@$parsed->columns = json_decode('{}');

		$sheet_section_info = $this->parse_sheet_section($objSheet);
		// var_dump($sheet_section_info);

		// --------------------
		// テーブル情報を読み取り
		$parsed->name = null;
		$parsed->label = null;
		$parsed->key_column = null;
		for( $idx = $sheet_section_info['table_info']['start']; $idx <= $sheet_section_info['table_info']['end']; $idx++ ){
			$tmp_info_key = strtolower(trim($objSheet->getCell('A'.$idx)->getCalculatedValue()));
			switch( $tmp_info_key ){
				case 'name':
				case 'label':
				case 'key_column':
					@$parsed->{$tmp_info_key} = $objSheet->getCell('B'.$idx)->getCalculatedValue();
					break;
			}
		}

		// --------------------
		// 定義列を読み取り
		$col_define = array();
		$col = 'A';
		$skip_count = 0;
		while(1){
			$def_key = $objSheet->getCell($col.$sheet_section_info['columns']['start'])->getCalculatedValue();
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
		for( $row_number = $sheet_section_info['columns']['start']+1; $row_number <= $sheet_section_info['columns']['end']; $row_number++ ){
			$col = json_decode('{}');
			foreach($col_define as $col_def_row){
				$col->{$col_def_row['key']} = $objSheet->getCell($col_def_row['col'].$row_number)->getCalculatedValue();
			}
			if(!@strlen($col->column_name)){
				break;
			}
			@$parsed->columns->{$col->column_name} = $col;
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
		foreach( $parsed->columns as $column_definition ){
			if( $column_definition->type == 'auto_id' || $column_definition->type == 'auto_increment' ){
				$parsed->system_columns->id = json_decode(json_encode(array(
					'type'=>$column_definition->type,
					'column_name'=>$column_definition->column_name,
				)));
				if( @is_null($parsed->key_column) ){
					$parsed->key_column = $column_definition->column_name;
				}
			}elseif( $column_definition->type == 'create_date' ){
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

	/**
	 * シートの全体構造を把握する
	 * @param  [type] $objSheet [description]
	 * @return [type]           [description]
	 */
	private function parse_sheet_section($objSheet){
		$sheet_section = array(
			'table_info'=>null,
			'columns'=>null,
			'sheet_max_row_number'=>$objSheet->getHighestRow(), // e.g. 10
			'sheet_max_col_name'=>$objSheet->getHighestColumn(), // e.g 'F'
		);

		$section_name_list = array(
			'table_info',
			'columns',
		);

		// 行番号を初期化
		// 基本的に、上から順にスキャンする.
		$row_number = 1;

		// --------------------
		// シートの全体構造をを読み取り
		$last_section_name = null;
		while(1){
			$def_key = $objSheet->getCell('A'.$row_number)->getCalculatedValue();
			foreach($section_name_list as $section_name){
				if(strtolower($def_key) == ':'.$section_name){
					$sheet_section[$section_name] = array(
						'start'=>$row_number+1,
					);
					if(strlen($last_section_name)){
						$sheet_section[$last_section_name]['end'] = ($row_number-1);
					}
					$last_section_name = $section_name;
				}
			}

			if( $row_number >= $sheet_section['sheet_max_row_number'] ){
				if(strlen($last_section_name)){
					$sheet_section[$last_section_name]['end'] = ($row_number);
				}
				break;
			}
			$row_number ++;
		}

		return $sheet_section;
	} // parse_sheet_section()

}
