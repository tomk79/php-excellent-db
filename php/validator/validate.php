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

		// --------------------------------------
		// 入力値のチェック
		foreach( $table_definition->columns as $column_definition ){
			if( !array_key_exists($column_definition->name, $data) ){
				// `$data` に含まれていないキーは調べない
				continue;
			}
			$value = @$data[$column_definition->name];

			// NOT NULL 制約
			if( !$column_definition->not_null && !strlen($value) ){
				// 必須項目ではなく、かつ値が入力されていない場合、エラーとして扱わない
				continue;
			}

			// EMAIL 形式チェック
			if( $column_definition->type == 'email' ){
				if( !preg_match('/^.*?\@.*?$/s', $value) ){
					$errors[$column_definition->name] = 'Invalid E-Mail Format.';
					continue;
				}
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

}
