<?php
require_once(__DIR__.'/../php/config.php');
$form = $exdb->get_form();
$form->automatic_signup_form(
	'user', // テーブル名
	array( // 初期登録するデータ
		'user_account',
		'user_name',
		'email',
		'password',
	),
	array(
		'href_backto'=>'./'
	)
);
