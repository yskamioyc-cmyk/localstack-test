FROM php:8.3-apache

# 1. 必要なパッケージのインストールとクリーンアップを一つのRUNにまとめる
# 理由: Dockerイメージのレイヤー数を減らし、イメージサイズを軽量化するため
RUN apt-get update && apt-get install -y --no-install-recommends \
    libonig-dev \
    libmariadb-dev \
    iputils-ping \
    awscli \
    && docker-php-ext-install pdo_mysql mysqli \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# 2. PHPの設定ファイルをコピー
COPY ./docker/php/php.ini /usr/local/etc/php/php.ini

# 3. 作業ディレクトリの指定（インフラ管理上、明示するのが望ましい）
WORKDIR /var/www/html