<?php
require_once(__DIR__.'/../php/config.php');
$form = $exdb->get_form();
$form->auth(
	// 照合するデータ
	array(
		'user.user_account',
		'user.password',
	)
);

print '<!DOCTYPE html>'."\n";
print '<p>Logged in.</p>'."\n";
exit();
