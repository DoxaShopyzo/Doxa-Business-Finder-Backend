<?php
/**
 * Doxa Business Finder - cPanel Environment & Database Repair Tool
 */
$baseDir = realpath(__DIR__ . '/../');
$envFile = $baseDir . '/.env';

$message = '';
$msgType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dbHost = trim($_POST['db_host'] ?? '127.0.0.1');
    $dbPort = trim($_POST['db_port'] ?? '3306');
    $dbName = trim($_POST['db_name'] ?? '');
    $dbUser = trim($_POST['db_user'] ?? '');
    $dbPass = trim($_POST['db_pass'] ?? '');

    // Test PDO connection
    try {
        $dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4";
        $pdo = new PDO($dsn, $dbUser, $dbPass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 5,
        ]);

        // Connection successful! Now update .env file
        if (file_exists($envFile)) {
            $envContent = file_get_contents($envFile);

            $replacements = [
                '/^DB_HOST=.*$/m' => "DB_HOST={$dbHost}",
                '/^DB_PORT=.*$/m' => "DB_PORT={$dbPort}",
                '/^DB_DATABASE=.*$/m' => "DB_DATABASE={$dbName}",
                '/^DB_USERNAME=.*$/m' => "DB_USERNAME={$dbUser}",
                '/^DB_PASSWORD=.*$/m' => "DB_PASSWORD={$dbPass}",
                '/^GEMINI_API_KEY=.*$/m' => "GEMINI_API_KEY=AQ.Ab8RN6L8-3KvIwAeDRPTNNDC79OO1OniB5mTCg-XnrSZpKHpuw",
                '/^GEMINI_MODEL=.*$/m' => "GEMINI_MODEL=gemini-3.6-flash",
            ];

            foreach ($replacements as $pattern => $replacement) {
                if (preg_match($pattern, $envContent)) {
                    $envContent = preg_replace($pattern, $replacement, $envContent);
                } else {
                    $envContent .= "\n" . $replacement;
                }
            }

            file_put_contents($envFile, $envContent);

            // Clear cache files if any
            $cacheFiles = glob($baseDir . '/bootstrap/cache/*.php');
            foreach ($cacheFiles as $cf) {
                if (basename($cf) !== '.gitignore') {
                    @unlink($cf);
                }
            }

            $message = "SUCCESS: Database connected & .env updated successfully! Your site is now LIVE.";
            $msgType = 'success';
        } else {
            $message = "ERROR: .env file not found at: " . htmlspecialchars($envFile);
            $msgType = 'danger';
        }
    } catch (Exception $e) {
        $message = "DATABASE CONNECTION FAILED: " . $e->getMessage();
        $msgType = 'danger';
    }
}

// Read current env DB values if available
$curDbHost = '127.0.0.1';
$curDbName = 'xlakngmy_businessfinder';
$curDbUser = 'xlakngmy_';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, 'DB_HOST=') === 0) $curDbHost = trim(substr($line, 8));
        if (strpos($line, 'DB_DATABASE=') === 0) $curDbName = trim(substr($line, 12));
        if (strpos($line, 'DB_USERNAME=') === 0) $curDbUser = trim(substr($line, 12));
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doxa Business Finder - cPanel DB Config</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #0f172a; color: #f8fafc; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; padding: 20px; box-sizing: border-box; }
        .card { background: #1e293b; border: 1px solid #334155; border-radius: 16px; width: 100%; max-width: 520px; padding: 32px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.5); }
        h2 { margin: 0 0 8px 0; color: #38bdf8; font-size: 24px; }
        p { color: #94a3b8; font-size: 14px; margin-top: 0; margin-bottom: 24px; }
        .alert { padding: 14px 18px; border-radius: 10px; font-size: 14px; margin-bottom: 20px; }
        .alert-success { background: #064e3b; border: 1px solid #059669; color: #6ee7b7; }
        .alert-danger { background: #7f1d1d; border: 1px solid #dc2626; color: #fca5a5; }
        .form-group { margin-bottom: 16px; }
        label { display: block; font-size: 13px; font-weight: 600; color: #cbd5e1; margin-bottom: 6px; }
        input { width: 100%; padding: 12px 14px; border-radius: 8px; border: 1px solid #475569; background: #0f172a; color: #f8fafc; font-size: 14px; box-sizing: border-box; }
        input:focus { outline: none; border-color: #38bdf8; box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.2); }
        .btn { width: 100%; padding: 14px; border: none; border-radius: 8px; background: #0284c7; color: white; font-weight: 700; font-size: 15px; cursor: pointer; transition: background 0.2s; margin-top: 8px; }
        .btn:hover { background: #0369a1; }
        .btn-live { display: inline-block; text-align: center; text-decoration: none; width: 100%; padding: 14px; border-radius: 8px; background: #10b981; color: white; font-weight: 700; font-size: 15px; margin-top: 12px; box-sizing: border-box; }
        .btn-live:hover { background: #059669; }
    </style>
</head>
<body>
    <div class="card">
        <h2>🛠️ cPanel Database Configuration</h2>
        <p>Set your cPanel MySQL Database credentials to immediately bring Doxa Business Finder back online.</p>

        <?php if ($message): ?>
            <div class="alert alert-<?= $msgType ?>">
                <?= htmlspecialchars($message) ?>
            </div>
            <?php if ($msgType === 'success'): ?>
                <a href="/admin/users" class="btn-live">👉 Go to Admin Users Page</a>
            <?php endif; ?>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Database Host</label>
                <input type="text" name="db_host" value="<?= htmlspecialchars($curDbHost) ?>" required>
            </div>
            <div class="form-group">
                <label>Database Name (from cPanel MySQL)</label>
                <input type="text" name="db_name" value="<?= htmlspecialchars($curDbName) ?>" placeholder="e.g. xlakngmy_businessfinder" required>
            </div>
            <div class="form-group">
                <label>Database Username (from cPanel MySQL Users)</label>
                <input type="text" name="db_user" value="<?= htmlspecialchars($curDbUser) ?>" placeholder="e.g. xlakngmy_dbuser" required>
            </div>
            <div class="form-group">
                <label>Database Password</label>
                <input type="password" name="db_pass" placeholder="Enter your cPanel DB Password" required>
            </div>
            <button type="submit" class="btn">Connect & Save to .env</button>
        </form>
    </div>
</body>
</html>
