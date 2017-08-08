<?php
require_once(__DIR__.'/../php/config.php');
$form = $exdb->get_form(array(
    'table'=>'user'
));
$form->automatic_form();
exit();
