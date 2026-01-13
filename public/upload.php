<?php
// 1. エラーをブラウザに強制表示させる（真っ白対策）
ini_set('display_errors', "On");
error_reporting(E_ALL);

// 2. パスの確認（DockerfileのWORKDIR /var/www/html と一致させる）
// composer install --working-dir=./public を実行しているため、
// vendor は /var/www/html/public/vendor にあります。
require_once __DIR__ . '/vendor/autoload.php';

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

// 3. .envを読み込む（ファイルが存在しない場合はスキップするように safeLoad）
try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->safeLoad();
} catch (Exception $e) {
    // .envがなくても続行（デフォルト値を使用）
}

// 4. S3クライアントの設定
$s3Client = new S3Client([
    'region'      => 'ap-northeast-1', // s3test.phpに合わせて変更
    'version'     => 'latest',
    'endpoint'    => 'http://localstack:4566',
    'use_path_style_endpoint' => true,
    'credentials' => [
        'key'    => 'test',
        'secret' => 'test',
    ],
]);

$message = "";

// 5. アップロード処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    try {
        $bucketName = 'my-test-bucket'; // 事前に作成したバケット名
        $file = $_FILES['image']['tmp_name'];
        $key = 'uploads/' . basename($_FILES['image']['name']);

        $result = $s3Client->putObject([
            'Bucket' => $bucketName,
            'Key'    => $key,
            'SourceFile' => $file,
            'ContentType' => $_FILES['image']['type'],
        ]);
        $message = "【成功】アップロードされました。URL: " . $result['ObjectURL'];
    } catch (AwsException $e) {
        $message = "【AWSエラー】" . $e->getAwsErrorMessage();
    } catch (Exception $e) {
        $message = "【一般エラー】" . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>S3 Image Upload Test</title>
    <style>
        body { font-family: sans-serif; background: #f4f4f4; padding: 20px; }
        .container { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); max-width: 500px; margin: auto; }
        .msg { padding: 10px; margin-bottom: 20px; border-radius: 4px; background: #e7f3ff; color: #004085; }
    </style>
</head>
<body>
    <div class="container">
        <h1>S3画像アップロード</h1>
        <?php if ($message): ?>
            <div class="msg"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <p>アップロードする画像を選択してください：</p>
            <input type="file" name="image" accept="image/*" required>
            <br><br>
            <button type="submit" style="padding: 10px 20px;">S3へアップロード</button>
        </form>
        <br>
        <a href="s3test.php">バケット状況を確認する</a>
    </div>
</body>
</html>