<?php
require __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;
use Aws\S3\S3Client;
use Aws\Exception\AwsException;

// 1. .envファイルの読み込み
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// 2. S3クライアントの初期化（.envから値を取得）
$s3Client = new S3Client([
    'region'      => $_ENV['AWS_REGION'] ?? 'ap-northeast-1',
    'version'     => 'latest',
    'credentials' => [
        'key'    => $_ENV['AWS_ACCESS_KEY'],
        'secret' => $_ENV['AWS_SECRET_KEY'],
    ],
    // LocalStackを使用するための重要な設定
    'endpoint'                => 'http://localstack:4566',
    'use_path_style_endpoint' => true,
]);

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>S3 Bucket List (LocalStack)</title>
    <style>
        body { font-family: sans-serif; line-height: 1.6; padding: 20px; }
        .success { color: green; font-weight: bold; }
        .error { color: red; }
    </style>
</head>
<body>
    <h1>S3 バケット一覧の取得テスト</h1>

    <?php
    try {
        // 3. バケット一覧を取得
        $result = $s3Client->listBuckets();
        
        echo '<p class="success">✅ LocalStackへの接続に成功しました！</p>';
        echo '<ul>';
        foreach ($result['Buckets'] as $bucket) {
            echo '<li>' . htmlspecialchars($bucket['Name']) . '（作成日: ' . $bucket['CreationDate'] . '）</li>';
        }
        echo '</ul>';
        
    } catch (AwsException $e) {
        // エラーが発生した場合
        echo '<p class="error">❌ エラーが発生しました：</p>';
        echo '<pre>' . htmlspecialchars($e->getMessage()) . '</pre>';
    }
    ?>
</body>
</html>