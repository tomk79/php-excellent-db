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

	// E-mail 形式チェック
	preg_match_all('/(\@)/', $user_input_value, $matched);
	if( !count($matched[1]) ){
		array_push($errors, 'アットマークが含まれていません。');
		return $errors;
	}
	if( count($matched[1]) > 1 ){
		array_push($errors, 'アットマークが複数含まれています。');
		return $errors;
	}
	if( preg_match('/^\@/s', $user_input_value) ){
		array_push($errors, 'アットマークの前が空白です。');
		return $errors;
	}
	if( preg_match('/\@$/s', $user_input_value) ){
		array_push($errors, 'アットマークの後が空白です。');
		return $errors;
	}
	if( !preg_match('/^\S+\@\S+$/s', $user_input_value) ){
		array_push($errors, '空白が含まれています。');
		return $errors;
	}
	return $errors;
};
