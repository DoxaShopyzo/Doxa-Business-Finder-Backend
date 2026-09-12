<?php
/**
 * Doxa Business Finder - Database Migration Tool for `search_results.website`
 * Modifies search_results.website from VARCHAR(191) to TEXT to prevent 500 truncation errors.
 */

$baseDir = realpath(__DIR__ . '/../');
$envFile = $baseDir . '/.env';

$message = '';
$msgType = 'info';
$columnType = 'Unknown';

if (file_exists($envFile)) {
    $envLines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $env = [];
    foreach ($envLines as $line) {
        if (str_starts_with(trim($line), '#')) continue;
        if (str_contains($line, '=')) {
            [$key, $val] = explode('=', $line, 2);
            $env[trim($key)] = trim($val, " \t\n\r\0\x0B\"'");
        }
    }

    $dbHost = $env['DB_HOST'] ?? '127.0.0.1';
    $dbPort = $env['DB_PORT'] ?? '3306';
    $dbName = $env['DB_DATABASE'] ?? '';
    $dbUser = $env['DB_USERNAME'] ?? '';
    $dbPass = $env['DB_PASSWORD'] ?? '';

    try {
        $dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4";
        $pdo = new PDO($dsn, $dbUser, $dbPass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 5,
        ]);

        // Check current column type
        $stmt = $pdo->query("SHOW COLUMNS FROM search_results LIKE 'website'");
        $col = $stmt->fetch(PDO::FETCH_ASSOC);
        $columnType = $col['Type'] ?? 'Not found';

        if (isset($_POST['execute_migration'])) {
            $pdo->exec("ALTER TABLE search_results MODIFY COLUMN website TEXT NULL");
            
            // Re-check
            $stmt = $pdo->query("SHOW COLUMNS FROM search_results LIKE 'website'");
            $col = $stmt->fetch(PDO::FETCH_ASSOC);
            $columnType = $col['Type'] ?? 'Unknown';

            $message = "Migration executed successfully! `website` column is now: " . strtoupper($columnType);
            $msgType = 'success';
        }
    } catch (PDOException $e) {
        $message = "Database connection error: " . $e->getMessage();
        $msgType = 'error';
    }
} else {
    $message = ".env file not found at " . $envFile;
    $msgType = 'error';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doxa - Search Results Table Migration</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #0f172a; color: #f8fafc; padding: 40px 20px; margin: 0; }
        .card { max-width: 600px; margin: 0 auto; background: #1e293b; border-radius: 16px; padding: 32px; box-shadow: 0 10px 25px rgba(0,0,0,0.5); border: 1px solid #334155; }
        h1 { margin-top: 0; font-size: 22px; color: #38bdf8; display: flex; align-items: center; gap: 10px; }
        .alert { padding: 14px 18px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; font-weight: 500; }
        .alert-success { background: #064e3b; border: 1px solid #059669; color: #34d399; }
        .alert-error { background: #450a0a; border: 1px solid #dc2626; color: #f87171; }
        .alert-info { background: #082f49; border: 1px solid #0284c7; color: #38bdf8; }
        .status-box { background: #0f172a; border-radius: 8px; padding: 16px; margin-bottom: 24px; border: 1px solid #334155; font-size: 14px; }
        .status-row { display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid #1e293b; }
        .status-row:last-child { border-bottom: none; }
        .status-val { font-family: monospace; font-weight: bold; color: #38bdf8; }
        button { width: 100%; padding: 14px; background: #2563eb; color: #fff; font-size: 15px; font-weight: 600; border: none; border-radius: 8px; cursor: pointer; transition: background 0.2s; }
        button:hover { background: #1d4ed8; }
        .danger-note { font-size: 12px; color: #94a3b8; text-align: center; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="card">
        <h1>🛠️ Doxa DB Migration Tool</h1>
        <p style="color: #94a3b8; font-size: 14px; margin-bottom: 24px;">
            Fix 500 search error by altering <code>search_results.website</code> from <code>VARCHAR(191)</code> to <code>TEXT</code>.
        </p>

        <?php if ($message): ?>
            <div class="alert alert-<?= $msgType ?>"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <div class="status-box">
            <div class="status-row">
                <span>Database:</span>
                <span class="status-val"><?= htmlspecialchars($dbName ?: 'Not configured') ?></span>
            </div>
            <div class="status-row">
                <span>Table:</span>
                <span class="status-val">search_results</span>
            </div>
            <div class="status-row">
                <span>Column:</span>
                <span class="status-val">website</span>
            </div>
            <div class="status-row">
                <span>Current Column Type:</span>
                <span class="status-val" style="color: <?= stripos($columnType, 'text') !== false ? '#34d399' : '#f59e0b' ?>;">
                    <?= htmlspecialchars($columnType) ?>
                </span>
            </div>
        </div>

        <form method="POST">
            <input type="hidden" name="execute_migration" value="1">
            <button type="submit">
                <?= stripos($columnType, 'text') !== false ? '✅ Already TEXT (Click to re-run)' : '🚀 Upgrade `website` to TEXT' ?>
            </button>
        </form>

        <p class="danger-note">
            ⚠️ Once run successfully, you can safely delete this file from cPanel.
        </p>
    </div>
</body>
</html>
