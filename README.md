# Excellent Database Manager

Excelファイルで定義したデータベーステーブル仕様に従って、データベース操作を実行します。

## 使い方 - Usage

※まだ開発中のため、この通りに組み込んでも動作しません。

```php
<?php
$pdo = new PDO( /* PDO Options */ );
$exdb = new excellent_db\create( $pdo, array(
	"prefix" => "your_prefix",
	"path_definition_file" => '/path/to/your/db_table_definition.xlsx',
	"path_cache_dir" => '/path/to/your/caches/',
) );
$exdb->migrate_init_tables();
```

## ライセンス - License

MIT License


## 作者 - Author

- Tomoya Koyanagi <tomk79@gmail.com>
- website: <http://www.pxt.jp/>
- Twitter: @tomk79 <http://twitter.com/tomk79/>
