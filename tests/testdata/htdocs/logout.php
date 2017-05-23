<?php
require_once(__DIR__.'/../php/config.php');
$form = $exdb->get_form();
$form->logout('user');
?>
<!DOCTYPE html>
<html>
<head>
<title>LOGOUT SAMPLE</title>
</head>
<body>
<p>Logged out.</p>
<p><a href="./">Back</a></p>
</body>
</html>
