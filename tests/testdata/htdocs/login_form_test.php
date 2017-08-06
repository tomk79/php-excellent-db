<?php
require_once(__DIR__.'/../php/config.php');
$form = $exdb->get_form();
$is_login = $form->auth(
	'user', // テーブル名
	array( // 照合するデータ
		'user_account',
		'password',
	)
);
if( !$is_login ){
	exit();
}
?>
<!DOCTYPE html>
<html>
<head>
<title>LOGIN SAMPLE</title>
</head>
<body>
<p>Logged in.</p>
<p><a href="./">Back</a></p>
<p><a href="logout.php">LOGOUT</a></p>
</body>
</html>
