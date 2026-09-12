<?php
// cPanel Laravel Fixer Script
echo "<h3>Doxa Business Finder - cPanel Fixer</h3>";

$basePath = realpath(__DIR__ . "/../");

// 1. Clear caches
$cacheDirs = [
    $basePath . "/bootstrap/cache",
    $basePath . "/storage/framework/views",
    $basePath . "/storage/framework/cache",
    $basePath . "/storage/framework/sessions"
];

foreach ($cacheDirs as $dir) {
    if (is_dir($dir)) {
        $files = glob($dir . "/*");
        foreach ($files as $file) {
            if (is_file($file) && basename($file) !== ".gitignore") {
                unlink($file);
            }
        }
    }
}
echo "<p>? Cleared all Laravel caches (bootstrap, views, sessions).</p>";

// 2. Set Storage Permissions
$storagePath = $basePath . "/storage";
if (is_dir($storagePath)) {
    exec("chmod -R 775 " . escapeshellarg($storagePath));
    echo "<p>? Set 775 permissions on storage directory.</p>";
}

// 3. Set Bootstrap Cache Permissions
$bootstrapPath = $basePath . "/bootstrap/cache";
if (is_dir($bootstrapPath)) {
    exec("chmod -R 775 " . escapeshellarg($bootstrapPath));
    echo "<p>? Set 775 permissions on bootstrap/cache directory.</p>";
}

echo "<p><strong>All fixes applied!</strong> Try visiting your site now.</p>";
?>
