<?php
/**
 * excellent-db: Validator
 */
namespace excellent_db;

/**
 * validator.php
 */
class validator{

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
	 * Validate Table Values
	 * @param  string $table_name テーブル名
	 * @param  array $input_data 入力データ
	 * @return array エラー配列
	 */
	public function validate_table($table_name, $input_data){
		$errors = array();

		$table_definition = $this->exdb->get_table_definition( $table_name );
		if( !@$table_definition ){
			$errors[':common'] = 'Table NOT Exists';
			return $errors;
		}

		// 余計な値が送られていないかチェック
		foreach( $input_data as $key=>$val ){
			if( !@$table_definition->columns->{$key} ){
				$errors[':common'] = '"'.$key.'" is NOT Exists in table "'.$table_name.'".';
			}
		}

		// --------------------------------------
		// 入力値のチェック
		foreach( $table_definition->columns as $column_definition ){
			if( !array_key_exists($column_definition->name, $input_data) ){
				// `$input_data` に含まれていないキーは調べない
				continue;
			}
			$value = @$input_data[$column_definition->name];

			$restrictions = array();
			if($column_definition->not_null){
				$restrictions['required'] = true;
			}
			$detect_errors = $this->detect_errors($value, $column_definition->type, $restrictions);

			if( count($detect_errors) ){
				$errors[$column_definition->name] = implode(' ', $detect_errors);
				continue;
			}

			// FOREIGN KEY 制約
			if( $column_definition->foreign_key ){
				$foreign_key = explode( '.', $column_definition->foreign_key );
				$foreign_row = $this->exdb->select(
					$foreign_key[0],
					array(
						$foreign_key[1] => $value,
					),
					array(
						'limit' => 1
					)
				);
				if( !$foreign_row ){
					$errors[$column_definition->name] = 'Foreign key is not exists.';
					continue;
				}
				unset($foreign_key, $foreign_row);
			}
		}

		return $errors;
	}

	/**
	 * Detect Input Errors
	 * @param string $value        Validation target value
	 * @param string $type         Data type
	 * @param array  $restrictions Restrictions
	 * @return array エラーメッセージを含む配列。 エラーがない場合、0件の配列が返ります。
	 */
	public function detect_errors($value, $type, $restrictions = array()){
		$errors = array();

		$is_required = @$restrictions['required'];
		if( $is_required && !strlen($value) ){
			// NOT NULL 制約
			// 空白文字も NULL と同様に扱う
			array_push($errors, 'Required.');
			return $errors;
		}
		if( !$is_required && !strlen($value) ){
			// NOT NULL 制約 がなくて値が空白の場合
			// 空白文字も NULL と同様に扱う
			return $errors;
		}

		$type_info = $this->exdb->form_elements()->get_type_info($type);
		if( is_callable($type_info['validate']) ){
			$errors = $type_info['validate']($value, $restrictions);
		}
		return $errors;
	}

}
