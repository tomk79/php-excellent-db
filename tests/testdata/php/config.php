<?php
mb_internal_encoding('utf-8');
@date_default_timezone_set('Asia/Tokyo');
require_once(__DIR__.'/../../../vendor/autoload.php');

$pdo = new \PDO(
	'sqlite:'.__DIR__.'/../_tmp/db/test.sqlite',
	null, null,
	array(
		\PDO::ATTR_PERSISTENT => false, // ←これをtrueにすると、"持続的な接続" になる
	)
);

$exdb = new excellent_db\create( $pdo, array(
	"prefix" => "excellent_test",
	"path_definition_file" => __DIR__.'/../db/sample_db_tables.xlsx',
	"path_cache_dir" => __DIR__.'/../_tmp/caches/',
) );
