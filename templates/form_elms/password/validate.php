<?php
/** Validator */
return function($user_input_value, $restrictions){
	$errors = array();
	if( strlen(@$restrictions['required']) ){
		if( !strlen($user_input_value) ){
			array_push($errors, '必須項目です。');
			return $errors;
		}
	}
	if( strlen(@$restrictions['max']) ){
		if( strlen($user_input_value) > intval($restrictions['max']) ){
			array_push($errors, $restrictions['max'].'バイト以内で入力してください。');
		}
	}
	if( strlen(@$restrictions['min']) ){
		if( strlen($user_input_value) < intval($restrictions['min']) ){
			array_push($errors, $restrictions['min'].'バイト以上で入力してください。');
		}
	}
	if( preg_match('/\r|\n|\r\n/', $user_input_value) ){
		array_push($errors, '改行が含まれています。');
	}
	return $errors;
};
