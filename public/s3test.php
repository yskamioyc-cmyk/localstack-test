<?php
// Localstackのエンドポイント
$endpoint = "http://localstack:4566";
$region = "ap-northeast-1";

// 修正ポイント1: コマンドを改行せず一行にまとめる
// 修正ポイント2: 2>&1 を追加して、エラーメッセージも $output に取り込めるようにする
$command = "AWS_ACCESS_KEY_ID=test AWS_SECRET_ACCESS_KEY=test aws s3 ls --endpoint-url=$endpoint --region $region 2>&1";

// コマンドを実行
exec($command, $output, $return_var);
?>

<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="UTF-8">
        <title>AWS LocalStack Status</title>
        <style>
            body { font-family: sans-serif;
            background: #f4f4f4; padding: 20px; }
            .container {background: white; padding: 20px;
            border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1) }
            h1 { color: #232f3e; border-bottom: 2px solid #ff9900;
            padding-bottom: 10px }
            pre { background: #272822; color: #f8f8f2; padding: 15px;
            border-radius: 5px; overflow-x: auto; }
            .status { font-weight: bold; color: green; }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>AWS LocalStack 稼働状況</h1>
            <p>ステータス: <span class="status">Connected (LocalStack)</span></p>

            <h3>S3 バケット一覧：</h3>
            <pre><?php
                if (empty($output)) { echo "バケットが見つかりません。";
                } else { echo htmlspecialchars(implode("\n", $output));
                    }
                    ?></pre>
                
                <h3>実行コマンド</h3>
                <code><?php echo htmlspecialchars($command); ?></code>
        </div>
    </body>
</html>