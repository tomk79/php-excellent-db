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

	/**
	 * constructor
	 *
	 * @param object $exdb ExcellentDb Object
	 * @param object $form_endpoint Form Endpoint Object
	 */
	public function __construct( $exdb, $form_endpoint, $table_name, $row_id ){
		$this->exdb = $exdb;
		$this->form_endpoint = $form_endpoint;
		$this->table_name = $table_name;
		$this->row_id = $row_id;
		$this->table_definition = $this->form_endpoint->get_current_table_definition();
		$this->query_options = $this->form_endpoint->get_query_options();
		return;
	}


	/**
	 * 編集画面を実行
	 * @return String HTML Source Code
	 */
	public function execute(){
		$options = $this->form_endpoint->get_options();
		$form_params = array_merge($options['get_params'], $options['post_params']);

		if( @$form_params['__action/'] == 'write' ){
			return $this->write();
		}elseif( @$form_params['__action/'] == 'done' ){
			return $this->done();
		}elseif( @$form_params['__action/'] == 'confirm' ){
			return $this->confirm();
		}
		return $this->input();
	} // page_edit()

	/**
	 * 編集画面を描画
	 * @return String HTML Source Code
	 */
	private function input(){
		// var_dump($table_name);
		$table_name = $this->table_name;
		$row_id = $this->row_id;

		$list = $this->exdb->select($table_name, array($this->table_definition->system_columns->id->column_name=>$row_id), $this->query_options);

		// var_dump($this->table_definition->system_columns);
		if( count($this->table_definition->system_columns->password) ){
			foreach( $list as $key=>$val ){
				foreach($this->table_definition->system_columns->password as $column_name){
					unset($list[$key][$column_name]);
				}
			}
		}
		$rtn = '';
		foreach( $this->table_definition->table_definition as $column_definition ){
			// var_dump($column_definition);
			$rtn .= $this->form_endpoint->render(
				'form_elms/default/edit.html',
				array(
					'value'=>@$list[0][$column_definition->column_name],
					'def'=>@$column_definition,
				)
			);
		}

		$rtn = $this->form_endpoint->render(
			'form_edit.html',
			array(
				'href_detail'=>$this->form_endpoint->generate_url($table_name, $row_id),
				'action'=>$this->form_endpoint->generate_url($table_name, $row_id, 'edit'),
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
	private function confirm(){
		// var_dump($table_name);
		$table_name = $this->table_name;
		$row_id = $this->row_id;

		$list = $this->exdb->select($table_name, array($this->table_definition->system_columns->id->column_name=>$row_id), $this->query_options);

		// var_dump($this->table_definition->system_columns);
		if( count($this->table_definition->system_columns->password) ){
			foreach( $list as $key=>$val ){
				foreach($this->table_definition->system_columns->password as $column_name){
					unset($list[$key][$column_name]);
				}
			}
		}
		$rtn = '';
		foreach( $this->table_definition->table_definition as $column_definition ){
			// var_dump($column_definition);
			$rtn .= $this->form_endpoint->render(
				'form_elms/default/detail.html',
				array(
					'value'=>@$list[0][$column_definition->column_name],
					'def'=>@$column_definition,
				)
			);
		}

		$rtn = $this->form_endpoint->render(
			'form_edit_confirm.html',
			array(
				'href_detail'=>$this->form_endpoint->generate_url($table_name, $row_id),
				'action'=>$this->form_endpoint->generate_url($table_name, $row_id, 'edit'),
				'content'=>$rtn,
			)
		);

		$rtn = $this->form_endpoint->wrap_theme($rtn);
		return $rtn;
	} // confirm()

	/**
	 * 書き込みを実行
	 * @return String HTML Source Code
	 */
	private function write(){
		$action = $this->form_endpoint->generate_url($this->table_name, $this->row_id, 'edit');
		@header('Location: '.$action.'?'.urlencode('__action/').'=done');
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

}
