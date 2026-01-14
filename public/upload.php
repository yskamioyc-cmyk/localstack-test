<?php
// 1. エラー表示設定
ini_set('display_errors', "On");
error_reporting(E_ALL);

require_once __DIR__ . '/vendor/autoload.php';

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

// 2. 環境変数の読み込み (.env)
try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->safeLoad();
} catch (Exception $e) {
    // .envがない場合はデフォルト値を使用
}

// 3. DB接続設定
// docker-compose.ymlのサービス名 'db' をホストに指定
$dsn = "mysql:host=db;dbname=" . ($_ENV['DB_NAME'] ?? 'local_db') . ";charset=utf8mb4";
$db_user = $_ENV['DB_USER'] ?? 'root';
$db_pass = $_ENV['DB_PASSWORD'] ?? 'password';

try {
    $pdo = new PDO($dsn, $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die("DB接続エラー: " . $e->getMessage());
}

// 4. S3クライアントの設定
$s3Client = new S3Client([
    'region'      => $_ENV['AWS_REGION'] ?? 'ap-northeast-1',
    'version'     => 'latest',
    'endpoint'    => 'http://localstack:4566', // LocalStackの共通ポート
    'use_path_style_endpoint' => true,
    'credentials' => [
        'key'    => 'test',
        'secret' => 'test',
    ],
]);

$message = "";

// 5. アップロード処理とDB登録
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    try {
        $bucketName = 'my-test-bucket'; 
        $fileName = basename($_FILES['image']['name']);
        $key = 'uploads/' . $fileName;

        // S3へアップロード
        $result = $s3Client->putObject([
            'Bucket' => $bucketName,
            'Key'    => $key,
            'SourceFile' => $_FILES['image']['tmp_name'],
            'ContentType' => $_FILES['image']['type'],
        ]);

        // ブラウザ表示用URL作成（コンテナ名のlocalstackをlocalhostに変換）
        $urlForDb = str_replace('localstack:4566', 'localhost:4566', $result['ObjectURL']);

        // DBに情報を保存
        $stmt = $pdo->prepare("INSERT INTO images (file_name, image_url) VALUES (?, ?)");
        $stmt->execute([$fileName, $urlForDb]);

        $message = "S3へのアップロードとDB登録に成功しました！";
    } catch (Exception $e) {
        $message = "エラー: " . $e->getMessage();
    }
}

// 6. 画像一覧の取得
$images = $pdo->query("SELECT * FROM images ORDER BY created_at DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>S3 & DB Image Manager</title>
    <style>
        body { font-family: sans-serif; background: #f4f4f4; padding: 20px; }
        .container { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); max-width: 800px; margin: auto; }
        .alert { padding: 10px; border-radius: 4px; margin-bottom: 20px; background: #e7f3ff; color: #004085; }
        .gallery { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 15px; margin-top: 20px; }
        .img-card { border: 1px solid #ddd; padding: 10px; background: #fff; text-align: center; border-radius: 4px; }
        .img-card img { max-width: 100%; height: 150px; object-fit: cover; border-radius: 2px; }
        .img-card p { font-size: 12px; color: #666; word-break: break-all; margin: 5px 0 0; }
        form { border-bottom: 2px solid #eee; padding-bottom: 20px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>S3画像アップロード & DB管理</h1>

        <?php if ($message): ?>
            <div class="alert"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <p>新しい画像をアップロード：</p>
            <input type="file" name="image" accept="image/*" required>
            <button type="submit">アップロード</button>
        </form>

        <h2>保存済み画像一覧</h2>
        <div class="gallery">
            <?php if (empty($images)): ?>
                <p>まだ画像はありません。</p>
            <?php else: ?>
                <?php foreach ($images as $img): ?>
                    <div class="img-card">
                        <img src="<?php echo htmlspecialchars($img['image_url']); ?>" alt="Image">
                        <p><?php echo htmlspecialchars($img['file_name']); ?></p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <p style="margin-top: 20px;">
            <a href="s3test.php">← S3バケット接続確認へ</a>
        </p>
    </div>
</body>
</html>