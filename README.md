# Excellent Database Manager

Excelファイルで定義したデータベーステーブル仕様に従って、データベース操作を実行します。

## Usage

```php
<?php
$exdb = new excellent_db\create(
	// DB Config.
	array(
		"dbms" => "sqlite",
		"host" => '/path/to/your/database.sqlite',
		"prefix" => "your_prefix",
		"path_cache_dir" => '/path/to/your/caches/',
	),
	// DB Table Definition.
	'/path/to/your/db_table_definition.xlsx'
);
```

## ライセンス - License

MIT License


## 作者 - Author

- Tomoya Koyanagi <tomk79@gmail.com>
- website: <http://www.pxt.jp/>
- Twitter: @tomk79 <http://twitter.com/tomk79/>
