## SHARE<br>
Twitter風 つぶやき共有アプリ(バックエンド）<br>
＊API サーバー（Laravel）<br>

## 概要

フロントエンドアプリケーション「SHARE」のRESTful APIサーバー。<br>

## 関連リポジトリ<br>

フロントエンドのリポジトリ<br>

https://github.com/yuri-th/sharehub.git

## 機能一覧<br>

ユーザー認証（Firebase Authentication）/投稿の一覧表示、追加処理、削除処理/いいね機能/コメント機能/バリデーション/レスポンシブデザイン（ブレイクポイント 768px)<br>

## 使用言語、フレームワーク、DB など<br>

PHP/Laravel (v8)/MySQL/Firebase<br>

## 環境構築<br>

1.MAMPの設定<br>
[MAMP](https://www.mamp.info/en/downloads/) をダウンロード・インストール。<br>
MAMPを起動して「Start Servers」をクリック。<br>

2.データベース作成<br>
ブラウザで `http://localhost/phpMyAdmin` にアクセスし、新しいデータベースを作成。

3.プロジェクトをコピーしたいディレクトリにてクローン<br>
「git clone <https://github.com/yuri-th/sharehub-backend.git>」<br>

4.依存関係をインストール<br>
composer install<br>

5.環境変数設定<br>
.env.exampleをコピーし、.env ファイルを編集。<br>
DB設定、Firebase設定などをする。<br>

6.アプリケーションキーの設定<br>
php artisan key:generate<br>

7.データベース設定・マイグレーション<br>
php artisan migrate<br>

8.サーバー起動<br>
php artisan serve<br>

## 追記事項<br>
MAMP以外の代替環境<br>
・XAMPP <br>
・Docker + Laravel Sail<br>
