# localstack-test

## １．概要
dockerを使用してLAMP環境を構築する工程の学習用リポジトリです。Localstackで擬似的なAWS環境も同時に構築します。

## ２．動作環境
以下の環境での動作を確認済みです。
* **OS**: windows 11 (25H2)
* **Docker**: version 28.5.2
* **Docker Compose**: version 2.40.3
* **LocalStack**:docker-compose.ymlファイル内で最新バージョンを指定（latest）。

## ３．セットアップ手順
1) リポジトリをクローン
2) `.env`ファイルをルートディレクトリ内に作成(4.を参照)
3) docker composeでコンテナを一括起動<br>
    以下のコマンドをルートディレクトリで実行してください。

     ```bash
     docker compose up -d
     ```

4) 動作確認方法<br>
     ブラウザで以下のURLにアクセスしてください。<br>
     **phpMyAdmin**: http://localhost:8081/ <br>
     **LocalStack Health**: http://localhost:4566/_localstack/health/
     
## ４．環境変数の設定
プロジェクトのルートに`.env`ファイルを作成し、以下の内容を設定してください。

```text
# MySQL設定
DB_ROOT_PASSWORD=root 
DB_NAME=test_db  # 任意
DB_USER=test_user  # 任意
DB_PASSWORD=test_password  #任意のパスワードを設定
DB_TIMEZONE=Asia/Tokyo

#AWS（Localstack）設定
AWS_REGION=ap-northeast-1
AWS_ACCESS_KEY=test
AWS_SECRET_KEY=test 
```
## ５．使い方/サンプル
* コンテナ起動後、http://localhost/s3test.php/ にアクセスすると、S3バケットの作成状況が表示されます。

  ![s3test.php](./images/s3test.png)

* phpMyAdminにアクセスすると、以下の画面が表示されます。サンプル通りの`.env`ファイルを作成した場合、
　「ユーザー名」="root"、「パスワード」="root"でログイン可能です。

<p align = center>
  <img src="./images/phpMyAdmin_login.png" width = 50% alt = "phpMyAdmin_login">
</p>

## ６．構成図
![システム構成図](./images/システム構成図_localstack-test.drawio.png)

dockerを使用した構成のため、ホストOSに影響を与えずに仮想環境の構築が可能です。

## ７．よくある質問

## ８．TODO/今後やりたいこと
* **2026-01-09**: 現在S3バケットの自動作成まで実現しているので、実際に画像データの格納機能などを実装する予定。

## ９．更新履歴
* **2026-01-14**: アップロードページの画像表示機能追加
* **2026-01-13**: S3バケットへの画像データアップロード機能を実装。
* **2026-01-09**: PHPコンテナへのComposerのインストール機能追加。
* **2026-01-09**: リポジトリ作成。

## ライセンス
このプロジェクトは[MITライセンス](LICENSE)のもとで公開されています。