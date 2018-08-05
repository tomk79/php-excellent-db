<?php
/**
 * excellent-db: Endpoint/Form
 */
namespace excellent_db;

/**
 * endpoint/form/edit.php
 */
class endpoint_form_edit{

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

	/** Row ID */
	private $row_id;

	/** Action Name (`edit` or `create`) */
	private $action_name;

	/**
	 * constructor
	 *
	 * @param object $exdb ExcellentDb Object
	 * @param object $form_endpoint Form Endpoint Object
	 * @param string $table_name Target Table Name
	 * @param string $row_id Target Row Unique ID
	 */
	public function __construct( $exdb, $form_endpoint, $table_name, $row_id = null ){
		$this->exdb = $exdb;
		$this->form_endpoint = $form_endpoint;
		$this->table_name = $table_name;
		$this->row_id = $row_id;
		$this->table_definition = $this->form_endpoint->get_current_table_definition();
		$this->query_options = $this->form_endpoint->get_query_options();
		$this->action_name = (strlen($row_id) ? 'edit' : 'create');
		return;
	}


	/**
	 * 編集画面を実行
	 * @return String HTML Source Code
	 */
	public function execute(){
		$options = $this->form_endpoint->get_options();
		$action = @$this->query_options['action'];
		$data = array();
		if( !strlen($action) && strlen($this->row_id) ){
			$data = @$this->form_endpoint->get_current_row_data();
		}else{
			$data = array_merge($data, $options['post_params']);
		}

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
		foreach( $this->table_definition->columns as $column_definition ){
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

			$type_info = $this->exdb->form_elements()->get_type_info($column_definition->type);
			$form_elm = $this->form_endpoint->render(
				$type_info['templates']['input'],
				array(
					'value'=>@$data[$column_definition->name],
					'name'=>@$column_definition->name,
					'def'=>@$column_definition,
					'select_options'=>@$foreign_key_array,
					'error'=>@$errors[$column_definition->name],
				)
			);
			$rtn .= $this->form_endpoint->render(
				'form_elms_unit.html',
				array(
					'label'=>@$column_definition->label,
					'content'=>$form_elm,
					'def'=>@$column_definition,
					'error'=>@$errors[$column_definition->name],
				)
			);
		}

		$rtn = $this->form_endpoint->render(
			'form_edit.html',
			array(
				'href_backto'=>$this->form_endpoint->generate_url($this->table_name, $this->row_id),
				'action'=>$this->form_endpoint->generate_url($this->table_name, $this->row_id, $this->action_name),
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
		foreach( $this->table_definition->columns as $column_definition ){
			// var_dump($column_definition);
			if( !$this->exdb->is_editable_column( $column_definition ) ){
				continue;
			}
			$type_info = $this->exdb->form_elements()->get_type_info($column_definition->type);
			$form_elm = $this->form_endpoint->render(
				$type_info['templates']['preview'],
				array(
					'value'=>@$data[$column_definition->name],
					'name'=>@$column_definition->name,
					'def'=>@$column_definition,
				)
			);
			$content .= $this->form_endpoint->render(
				'form_elms_unit.html',
				array(
					'label'=>@$column_definition->label,
					'content'=>$form_elm,
					'error'=>null,
				)
			);
			$hidden .= '<input type="hidden" name="'.htmlspecialchars($column_definition->name).'" value="'.htmlspecialchars(@$data[$column_definition->name]).'"/>';
		}

		$rtn = $this->form_endpoint->render(
			'form_edit_confirm.html',
			array(
				'href_backto'=>$this->form_endpoint->generate_url($this->table_name, $this->row_id),
				'action'=>$this->form_endpoint->generate_url($this->table_name, $this->row_id, $this->action_name),
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

		$action = $this->form_endpoint->generate_url($this->table_name, $this->row_id, $this->action_name);
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
				'href_backto'=>$this->form_endpoint->generate_url($this->table_name, $this->row_id),
			)
		);

		$rtn = $this->form_endpoint->wrap_theme($rtn);
		return $rtn;
	} // done()

}
