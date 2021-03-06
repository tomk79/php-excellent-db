# Excellent Database Manager

Excelファイルで定義したデータベーステーブル仕様に従って、データベース操作を実行します。

## 使い方 - Usage

※まだ開発中のため、この通りに組み込んでも動作しません。

### 初期化処理

```php
<?php
require_once('/path/to/vendor/autoload.php');

// 初期化
$pdo = new PDO( /* PDO Options */ );
$exdb = new excellent_db\create( $pdo, array(
	// テーブル名の接頭辞
	"prefix" => "your_prefix",
	// データベース設計書
	"path_definition_file" => '/path/to/your/db_table_definition.xlsx',
	// キャッシュディレクトリ
	"path_cache_dir" => '/path/to/your/caches/',
) );

// データベーステーブルを初期化
$exdb->migrate_init_tables();
```

Excellent Db は、 データベース設計書 に記述された物理設計に従って、フォーム や REST API を自動生成します。

データベース設計書の定義については、[こちらを参照](./docs/excel_format.md)してください。

### REST API 自動処理

```php
<?php
require_once('/path/to/vendor/autoload.php');
$pdo = new PDO( /* PDO Options */ );
$exdb = new excellent_db\create( $pdo, /* options */ );

$rest = $exdb->get_rest();
$rest->automatic_rest_api();
exit();
```

### フォームAPI 自動処理

#### サインアップフォーム

```php
<?php
require_once('/path/to/vendor/autoload.php');
$pdo = new PDO( /* PDO Options */ );
$exdb = new excellent_db\create( $pdo, /* options */ );

$form = $exdb->get_form();
$form->automatic_signup_form(
	'user', // テーブル名
	array( // 初期登録するカラム
		'user_account',
		'user_name',
		'email',
		'password',
	),
	array( // Options
		'href_backto'=>'/' // 戻り先のURL
	)
);
exit();
```

#### ログインフォーム

```php
<?php
require_once('/path/to/vendor/autoload.php');
$pdo = new PDO( /* PDO Options */ );
$exdb = new excellent_db\create( $pdo, /* options */ );

$form = $exdb->get_form();
$form->auth(
	'user', // テーブル名
	array( // 照合するデータ
		'user_account',
		'password',
	)
);
?>
<!DOCTYPE html>
<html>
<head>
<title>LOGIN SAMPLE</title>
</head>
<body>
<p>Logged in.</p>
</body>
</html>
```

#### ログアウト

```php
<?php
require_once('/path/to/vendor/autoload.php');
$pdo = new PDO( /* PDO Options */ );
$exdb = new excellent_db\create( $pdo, /* options */ );

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
</body>
</html>
```

#### フォーム自動処理

```php
<?php
require_once('/path/to/vendor/autoload.php');
$pdo = new PDO( /* PDO Options */ );
$exdb = new excellent_db\create( $pdo, /* options */ );

$form = $exdb->get_form();
$form->automatic_form();
exit();
```

#### validator を単体で使う

```php
<?php
require_once('/path/to/vendor/autoload.php');
$pdo = new PDO( /* PDO Options */ );
$exdb = new excellent_db\create( $pdo, /* options */ );
$validator = $exdb->validator();

$check_value = 'valid';
$errors = $validator->detect_errors(
	$check_value, // 検証対象の値
	'text', // 期待するデータ型
	array( // 制約事項 (optional)
		'not_null'=>true
	)
);
if( count($errors) ){
	echo "Invalid";
}else{
	echo "Valid";
}
```


## ライセンス - License

MIT License


## 作者 - Author

- Tomoya Koyanagi <tomk79@gmail.com>
- website: <http://www.pxt.jp/>
- Twitter: @tomk79 <http://twitter.com/tomk79/>
