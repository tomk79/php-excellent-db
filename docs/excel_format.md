# Excel Format

<img src="images/excel_image.png" />

## Overview

- 1シートにつき1つのテーブルを表現します。

## `:table_info` - Table Definition

### name

物理テーブル名。

実際には、 `$exdb` に渡されたオプション `prefix` と組み合わせ、 `$prefix.'_'.$table_name` の規則でテーブル名が定義されます。

### label

論理テーブル名。

### key_column

キーとするカラム名。
省略時は、はじめて見つけた `auto_increment` または `auto_id` のカラムをキーとして設定します。

## `:columns` - Column Definition

### name

カラム名。
実際に生成されるテーブル上でのカラムの物理名称として使われます。

### type

カラムの種類。
一般的なDBMSが定義するデータ型ですが、 Excellent Db が拡張する論理的な型が幾つか追加されています。

詳しくは [Column Types](column_types.md) を参照。

### label

カラムの論理名称。フォーム生成時に入力欄のラベルとして使用されます。

### description

カラムの説明。人語で記述します。

### unique

`1` をセットして、unique 制約をつけます。

### hidden

`1` をセットして、非表示にします。
非表示にされたカラムは、フォームや一覧に表示されなくなります。
エンドユーザーは意識する必要がなく、アプリケーションが自動的に操作するカラムに設定します。

### not_null

`1` をセットして、 NOT NULL 制約をつけます。

### foreign_key

foreign key 制約。外部キーとするテーブル名とカラム名を記述します。
