<?php
require_once(__DIR__.'/../php/config.php');
$rest = $exdb->get_rest();
$rest->automatic_rest_api();
exit();
