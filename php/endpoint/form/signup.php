<?php
/**
 * excellent-db: Endpoint/Form
 */
namespace excellent_db;

/**
 * endpoint/form/signup.php
 */
class endpoint_form_signup{

	/** ExcellentDb Object */
	private $exdb;

	/** Form Endpoint Object */
	private $form_endpoint;

	/** Query Options */
	private $query_options;

	/** Table Definition */
	private $table_definition;

	/** Table Name */
	private $table_name;

	/** 初期設定するカラム名 */
	private $init_cols;

	/** Action Name (`edit` or `create`) */
	private $action_name;

	/**
	 * constructor
	 *
	 * @param object $exdb ExcellentDb Object
	 * @param object $form_endpoint Form Endpoint Object
	 * @param string $table_name Target Table Name
	 * @param array $init_cols 初期設定するカラム名
	 */
	public function __construct( $exdb, $form_endpoint, $table_name, $init_cols = array() ){
		$this->exdb = $exdb;
		$this->form_endpoint = $form_endpoint;
		$this->table_name = $table_name;
		$this->init_cols = $init_cols;
		$this->table_definition = $this->form_endpoint->get_current_table_definition();
		$this->query_options = $this->form_endpoint->get_query_options();
		$this->action_name = 'create';
		return;
	}


	/**
	 * 編集画面を実行
	 * @return String HTML Source Code
	 */
	public function execute(){
		$options = $this->form_endpoint->get_options();
		$action = @$this->query_options['action'];
		$data = $options['post_params'];

		$errors = $this->exdb->validate($this->table_name, $data);

		if( $action == 'write' ){
			if( count($errors) ){
				// validation でエラーが検出されたら、入力画面に戻る。
				return $this->input($data, $errors);
			}
			return $this->write($data);
		}elseif( $action == 'done' ){
			return $this->done();
		}elseif( $action == 'confirm' ){
			if( count($errors) ){
				// validation でエラーが検出されたら、入力画面に戻る。
				return $this->input($data, $errors);
			}
			return $this->confirm($data);
		}
		return $this->input($data);
	} // execute()

	/**
	 * 編集画面を描画
	 * @param  array $data 入力データ
	 * @param  array $errors エラー配列
	 * @return String HTML Source Code
	 */
	private function input($data, $errors = array()){
		$rtn = '';
		foreach( $this->init_cols as $column_name ){
			$column_definition = $this->table_definition->columns->$column_name;
			// var_dump($column_definition);
			if( !$this->exdb->is_editable_column( $column_definition ) ){
				continue;
			}

			$foreign_key_array = null;
			if( $column_definition->foreign_key ){
				$foreign_key = explode( '.', $column_definition->foreign_key );
				$foreign_row = $this->exdb->select(
					$foreign_key[0],
					array(),
					array('limit' => 10000)
				);
				if( is_array($foreign_row) ){
					foreach($foreign_row as $foreign_row_row){
						$foreign_key_array[$foreign_row_row[$foreign_key[1]]] = $foreign_row_row[$foreign_key[1]];
					}
				}
			}

			$rtn .= $this->form_endpoint->render(
				'form_elms/default/edit.html',
				array(
					'value'=>@$data[$column_definition->name],
					'error'=>@$errors[$column_definition->name],
					'def'=>@$column_definition,
					'select_options'=>@$foreign_key_array,
				)
			);
		}

		$rtn = $this->form_endpoint->render(
			'form_edit.html',
			array(
				'href_detail'=>'?',
				'action'=>'?',
				'error'=>@$errors[':common'],
				'content'=>$rtn,
			)
		);

		$rtn = $this->form_endpoint->wrap_theme($rtn);
		return $rtn;
	} // input()

	/**
	 * 確認画面を描画
	 * @param  array $data 入力データ
	 * @return String HTML Source Code
	 */
	private function confirm($data){
		$content = '';
		$hidden = '';
		foreach( $this->init_cols as $column_name ){
			$column_definition = $this->table_definition->columns->$column_name;
			// var_dump($column_definition);
			if( !$this->exdb->is_editable_column( $column_definition ) ){
				continue;
			}
			$content .= $this->form_endpoint->render(
				'form_elms/default/detail.html',
				array(
					'value'=>@$data[$column_definition->name],
					'def'=>@$column_definition,
				)
			);
			$hidden .= '<input type="hidden" name="'.htmlspecialchars($column_definition->name).'" value="'.htmlspecialchars(@$data[$column_definition->name]).'"/>';
		}

		$rtn = $this->form_endpoint->render(
			'form_edit_confirm.html',
			array(
				'href_detail'=>'?',
				'action'=>'?',
				'content'=>$content,
				'hidden'=>$hidden,
			)
		);

		$rtn = $this->form_endpoint->wrap_theme($rtn);
		return $rtn;
	} // confirm()

	/**
	 * 書き込みを実行
	 * @param  array $data 入力データ
	 * @return String HTML Source Code
	 */
	private function write($data){
		// var_dump($data);
		if( $this->action_name == "create" ){
			$result = $this->exdb->insert(
				$this->table_name,
				$data
			);
		}elseif( $this->action_name == "edit" ){
			$result = $this->exdb->update(
				$this->table_name,
				array($this->table_definition->key_column=>$this->row_id),
				$data
			);
		}

		if( !$result ){
			// 書き込みに失敗
			$errors = array();
			$errors[':common'] = 'Sorry, failed to save changes. Please try again later.';
			return $this->input($data, $errors);
		}

		if( !is_null( @$data[$this->table_definition->key_column] ) ){
			// キーの値を変更している場合は更新する
			$this->row_id = $data[$this->table_definition->key_column];
		}

		$action = '';
		@header('Location: '.$action.'?'.urlencode(':action').'=done');
		return '';
	} // write()

	/**
	 * 完了画面を描画
	 * @return String HTML Source Code
	 */
	private function done(){
		$rtn = $this->form_endpoint->render(
			'form_edit_done.html',
			array(
				'href_detail'=>'?',
			)
		);

		$rtn = $this->form_endpoint->wrap_theme($rtn);
		return $rtn;
	} // done()

}
