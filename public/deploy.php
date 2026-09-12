<?php
/**
 * Doxa Business Finder - Automated GitHub Webhook Deployment
 * Triggers automatic pull and file deployment whenever code is pushed to GitHub.
 */
header('Content-Type: application/json');

$secret = 'DoxaDeploy2026';

if (($_GET['secret'] ?? '') !== $secret && ($_SERVER['HTTP_X_DOXA_TOKEN'] ?? '') !== $secret) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$repoPath = '/home1/xlakngmy/repositories/doxa-backend';
$deployPath = '/home1/xlakngmy/public_html';

$pullOutput = [];
exec("git -C " . escapeshellarg($repoPath) . " pull origin main 2>&1", $pullOutput, $pullCode);

$syncOutput = [];
exec("rsync -av --exclude='.git*' --exclude='.cpanel.yml' --exclude='.env' --exclude='storage/logs/*' " . escapeshellarg($repoPath) . "/ " . escapeshellarg($deployPath) . "/ 2>&1", $syncOutput);

// Ensure public files go to the public_html/businessfinder directory
exec("cp -R " . escapeshellarg($repoPath . "/public") . "/* " . escapeshellarg($deployPath . "/businessfinder") . "/ 2>&1");

// Clear Laravel caches
$cacheOutput = [];
exec("php " . escapeshellarg($deployPath . "/artisan") . " cache:clear 2>&1", $cacheOutput);

echo json_encode([
    'status' => 'success',
    'message' => 'Deployed successfully to live server without opening cPanel!',
    'time' => date('Y-m-d H:i:s'),
    'git_pull' => $pullOutput,
    'cache_clear' => $cacheOutput,
]);
