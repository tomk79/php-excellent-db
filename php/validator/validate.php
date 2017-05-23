<?php
/**
 * excellent-db: Validator/Validate
 */
namespace excellent_db;

/**
 * validator/validate.php
 */
class validator_validate{

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
	 * Do Validation
	 * @param  string $table_name テーブル名
	 * @param  array $data 入力データ
	 * @return array エラー配列
	 */
	public function validate($table_name, $data){
		$errors = array();

		$table_definition = $this->exdb->get_table_definition( $table_name );
		if( !@$table_definition ){
			$errors[':common'] = 'Table NOT Exists';
			return $errors;
		}

		// 余計な値が送られていないかチェック
		foreach( $data as $key=>$val ){
			if( !@$table_definition->columns->{$key} ){
				$errors[':common'] = '"'.$key.'" is NOT Exists in table "'.$table_name.'".';
			}
		}

		// 形式のチェック
		foreach( $table_definition->columns as $column_definition ){
			if( !array_key_exists($column_definition->column_name, $data) ){
				// `$data` に含まれていないキーは調べない
				continue;
			}
			$value = @$data[$column_definition->column_name];

			if( !$column_definition->not_null && !strlen($value) ){
				// 必須項目ではなく、かつ値が入力されていない場合、エラーとして扱わない
				continue;
			}

			if( $column_definition->type == 'email' ){
				if( !preg_match('/^.*?\@.*?$/s', $value) ){
					$errors[$column_definition->column_name] = 'Invalid E-Mail Format.';
				}
			}
		}

		return $errors;
	}

}
