<?php
// 1. エラー表示設定
ini_set('display_errors', "On");
error_reporting(E_ALL);

// 2. ライブラリの読み込み（余計な記号をすべて削除しました）
require_once __DIR__ . '/vendor/autoload.php';

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

// 3. .envの読み込み
try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->safeLoad();
} catch (Exception $e) {
    // .envがない場合は無視して進む
}

// 4. S3クライアントの設定
$s3Client = new S3Client([
    'region'      => 'ap-northeast-1',
    'version'     => 'latest',
    'endpoint'    => 'http://localstack:4566',
    'use_path_style_endpoint' => true,
    'credentials' => [
        'key'    => 'test',
        'secret' => 'test',
    ],
]);

$message = "";
$previewUrl = "";

// 5. アップロード処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    try {
        $bucketName = 'my-test-bucket'; 
        $file = $_FILES['image']['tmp_name'];
        $key = 'uploads/' . basename($_FILES['image']['name']);

        $result = $s3Client->putObject([
            'Bucket' => $bucketName,
            'Key'    => $key,
            'SourceFile' => $file,
            'ContentType' => $_FILES['image']['type'],
        ]);

        $message = "アップロードに成功しました！";
        // ブラウザ表示用にURLを変換
        $previewUrl = str_replace('localstack:4566', 'localhost:4566', $result['ObjectURL']);

    } catch (AwsException $e) {
        $message = "【AWSエラー】" . $e->getAwsErrorMessage();
    } catch (Exception $e) {
        $message = "【エラー】" . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>S3 Image Upload</title>
    <style>
        body { font-family: sans-serif; background: #f4f4f4; padding: 20px; }
        .container { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); max-width: 500px; margin: auto; }
        .success { color: #004085; background: #e7f3ff; padding: 10px; border-radius: 4px; margin-bottom: 20px; }
        .preview-box { margin-top: 20px; text-align: center; border-top: 1px solid #eee; padding-top: 20px; }
        .preview-box img { max-width: 100%; height: auto; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>S3画像アップロード</h1>

        <?php if ($message): ?>
            <div class="success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <input type="file" name="image" accept="image/*" required>
            <br><br>
            <button type="submit">S3へアップロード</button>
        </form>

        <?php if ($previewUrl): ?>
            <div class="preview-box">
                <p>アップロードされた画像：</p>
                <img src="<?php echo htmlspecialchars($previewUrl); ?>" alt="uploaded image">
                <p><small>URL: <?php echo htmlspecialchars($previewUrl); ?></small></p>
            </div>
        <?php endif; ?>

        <br>
        <a href="s3test.php">バケット状況を確認する</a>
    </div>
</body>
</html>