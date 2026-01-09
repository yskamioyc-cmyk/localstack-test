FROM php:8.3-apache

# 1. 依存パッケージのインストール
# zlib1g-dev は libzip-dev と共に zip 拡張のインストールに必要になる場合があるため追加
RUN apt-get update && apt-get install -y --no-install-recommends \
    libonig-dev \
    libmariadb-dev \
    iputils-ping \
    awscli \
    git \
    unzip \
    libzip-dev \
    zlib1g-dev \
    && docker-php-ext-install pdo_mysql mysqli zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# 2. Composerのインストール
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 3. Apacheの設定変更（ドキュメントルートの変更）
# 実行権限などの影響を受けにくいよう、早い段階で設定を書き換えておきます
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# 4. PHPの設定ファイルをコピー
COPY ./docker/php/php.ini /usr/local/etc/php/php.ini

# 5. 作業ディレクトリの指定
WORKDIR /var/www/html

# 6. Composer依存関係のインストール
# 先にファイルだけコピーして install することで、ソースコードに変更があっても
# ライブラリのインストール工程をキャッシュから再利用でき、ビルドが速くなります
COPY public/composer.json public/composer.lock ./public/
RUN composer install --no-interaction --optimize-autoloader --working-dir=./public

# 7. プロジェクトファイルのコピー（必要に応じて）
# ローカル開発では docker-compose の volumes で上書きされますが、
# Docker単体で動かす際や本番ビルドを見据えるなら記述しておきます
# COPY . .