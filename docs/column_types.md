# Column Types

[Excel Format](excel_format.md) に記載のある `type` に設定可能な値のリストを定義します。

## auto_increment

自動的に割り当てられる数値型のキー。

## auto_id

自動的に割り当てられる文字列型のキー。

## text

TEXT 型。
可変長の単一行入力。

## textarea

`text` と同じが、可変長の複数行入力ができる。

## password

`text` と同じが、入力時に文字列が伏せられる。

## select

与えられる選択肢からの選択。

## date

日付型の入力。

## datetime

日時型の入力。

## create_date

行の生成日時。

## update_date

行の最終更新日時。

## delete_date

行の削除日時。

## delete_flg

行の論理削除フラグ。
