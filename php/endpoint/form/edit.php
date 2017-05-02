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
		$form_params = array_merge($options['get_params'], $options['post_params']);
		$action = @$form_params[':action'];
		$data = array();
		if( !strlen($action) && strlen($this->row_id) ){
			$list = $this->exdb->select($this->table_name, array($this->table_definition->system_columns->id->column_name=>$this->row_id), $this->query_options);

			// var_dump($this->table_definition->system_columns);
			if( count($this->table_definition->system_columns->password) ){
				foreach( $list as $key=>$val ){
					foreach($this->table_definition->system_columns->password as $column_name){
						unset($list[$key][$column_name]);
					}
				}
			}
			$data = @$list[0];
		}else{
			$data = array_merge($data, $options['post_params']);
		}
		unset($data[':action']);

		if( $action == 'write' ){
			return $this->write($data);
		}elseif( $action == 'done' ){
			return $this->done();
		}elseif( $action == 'confirm' ){
			return $this->confirm($data);
		}
		return $this->input($data);
	} // page_edit()

	/**
	 * 編集画面を描画
	 * @return String HTML Source Code
	 */
	private function input($data){
		$rtn = '';
		foreach( $this->table_definition->table_definition as $column_definition ){
			// var_dump($column_definition);
			if( !$this->is_editable_column( $column_definition ) ){
				continue;
			}
			$rtn .= $this->form_endpoint->render(
				'form_elms/default/edit.html',
				array(
					'value'=>@$data[$column_definition->column_name],
					'def'=>@$column_definition,
				)
			);
		}

		$rtn = $this->form_endpoint->render(
			'form_edit.html',
			array(
				'href_detail'=>$this->form_endpoint->generate_url($this->table_name, $this->row_id),
				'action'=>$this->form_endpoint->generate_url($this->table_name, $this->row_id, $this->action_name),
				'content'=>$rtn,
			)
		);

		$rtn = $this->form_endpoint->wrap_theme($rtn);
		return $rtn;
	} // input()

	/**
	 * 確認画面を描画
	 * @return String HTML Source Code
	 */
	private function confirm($data){
		$content = '';
		$hidden = '';
		foreach( $this->table_definition->table_definition as $column_definition ){
			// var_dump($column_definition);
			if( !$this->is_editable_column( $column_definition ) ){
				continue;
			}
			$content .= $this->form_endpoint->render(
				'form_elms/default/detail.html',
				array(
					'value'=>@$data[$column_definition->column_name],
					'def'=>@$column_definition,
				)
			);
			$hidden .= '<input type="hidden" name="'.htmlspecialchars($column_definition->column_name).'" value="'.htmlspecialchars(@$data[$column_definition->column_name]).'"/>';
		}

		$rtn = $this->form_endpoint->render(
			'form_edit_confirm.html',
			array(
				'href_detail'=>$this->form_endpoint->generate_url($this->table_name, $this->row_id),
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
				array($this->table_definition->system_columns->id->column_name=>$this->row_id),
				$data
			);
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
				'href_detail'=>$this->form_endpoint->generate_url($this->table_name, $this->row_id),
			)
		);

		$rtn = $this->form_endpoint->wrap_theme($rtn);
		return $rtn;
	} // done()

	/**
	 * 編集可能なカラムか調べる
	 * @param  object  $column_definition カラム定義
	 * @return boolean                    編集可否
	 */
	private function is_editable_column( $column_definition ){
		if($column_definition->type == 'auto_id'){
			return false;
		}elseif($column_definition->type == 'auto_increment'){
			return false;
		}elseif($column_definition->type == 'create_date'){
			return false;
		}elseif($column_definition->type == 'update_date'){
			return false;
		}elseif($column_definition->type == 'delete_date'){
			return false;
		}elseif($column_definition->type == 'delete_flg'){
			return false;
		}
		return true;
	}

}
