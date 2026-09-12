<?php
/**
 * Doxa Business Finder - Admin Password Reset Tool
 * Place in public/ directory. Allows administrator to reset or create super admin credentials safely.
 */

$baseDir = realpath(__DIR__ . '/../');
$envFile = $baseDir . '/.env';

$message = '';
$msgType = 'info';
$adminUsers = [];

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

        // Process Reset Request
        if (isset($_POST['reset_password'])) {
            $targetEmail = trim($_POST['target_email'] ?? 'admin@doxainfoplus.com');
            $newPassword = $_POST['new_password'] ?? 'Admin@123456';

            if (!empty($targetEmail) && !empty($newPassword)) {
                $hash = password_hash($newPassword, PASSWORD_BCRYPT);
                
                // Check if user exists
                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
                $stmt->execute([$targetEmail]);
                $existing = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($existing) {
                    $update = $pdo->prepare("UPDATE users SET password = ?, is_super_admin = 1, role = 'super_admin', status = 'active', email_verified_at = COALESCE(email_verified_at, NOW()) WHERE id = ?");
                    $update->execute([$hash, $existing['id']]);
                    $message = "Success! Password for '{$targetEmail}' has been updated to '{$newPassword}'. You can log in now.";
                    $msgType = 'success';
                } else {
                    $insert = $pdo->prepare("INSERT INTO users (name, email, password, role, is_super_admin, status, onboarding_completed, email_verified_at, created_at, updated_at) VALUES (?, ?, ?, 'super_admin', 1, 'active', 1, NOW(), NOW(), NOW())");
                    $insert->execute(['Super Admin', $targetEmail, $hash]);
                    $message = "Success! Created super admin account '{$targetEmail}' with password '{$newPassword}'.";
                    $msgType = 'success';
                }
            } else {
                $message = "Please provide both an email and a password.";
                $msgType = 'error';
            }
        }

        // Fetch all admin and doxainfo accounts
        $stmt = $pdo->query("SELECT id, name, email, role, is_super_admin, status, created_at FROM users WHERE is_super_admin = 1 OR role = 'super_admin' OR role = 'admin' OR email LIKE '%doxa%' OR email LIKE '%admin%'");
        $adminUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
    <title>Doxa Admin Password Reset Tool</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; background: #0b1320; }</style>
</head>
<body class="min-h-screen text-slate-100 flex flex-col justify-center items-center p-4">
    <div class="max-w-2xl w-full bg-slate-900 border border-slate-800 rounded-2xl shadow-2xl p-8">
        <div class="flex items-center space-x-3 mb-6">
            <div class="w-10 h-10 rounded-xl bg-cyan-500/10 text-cyan-400 flex items-center justify-center font-bold text-xl border border-cyan-500/20">
                🔒
            </div>
            <div>
                <h1 class="text-2xl font-bold text-white">Super Admin Password Reset</h1>
                <p class="text-xs text-slate-400">Doxa Business Finder &bull; Emergency Access Tool</p>
            </div>
        </div>

        <?php if (!empty($message)): ?>
            <div class="mb-6 p-4 rounded-xl border <?php echo $msgType === 'success' ? 'bg-emerald-950/50 border-emerald-500/30 text-emerald-300' : 'bg-rose-950/50 border-rose-500/30 text-rose-300'; ?>">
                <div class="flex items-center space-x-2">
                    <span><?php echo $msgType === 'success' ? '✅' : '⚠️'; ?></span>
                    <span class="font-medium text-sm"><?php echo htmlspecialchars($message); ?></span>
                </div>
                <?php if ($msgType === 'success'): ?>
                    <div class="mt-3">
                        <a href="/login" class="inline-block px-4 py-2 bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold rounded-lg text-xs transition">
                            Go to Admin Login &rarr;
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Quick 1-Click Form -->
        <form method="POST" class="space-y-4 mb-8 bg-slate-800/50 p-6 rounded-xl border border-slate-700/50">
            <h2 class="text-sm font-semibold uppercase tracking-wider text-cyan-400">Reset or Create Admin Credentials</h2>
            
            <div>
                <label class="block text-xs text-slate-300 mb-1">Admin Email Address</label>
                <select name="target_email" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-cyan-500">
                    <option value="admin@doxainfoplus.com" selected>admin@doxainfoplus.com</option>
                    <option value="Doxainfotech@gmail.com">Doxainfotech@gmail.com</option>
                    <option value="admin@doxainfotech.com">admin@doxainfotech.com</option>
                </select>
            </div>

            <div>
                <label class="block text-xs text-slate-300 mb-1">New Password</label>
                <input type="text" name="new_password" value="Admin@123456" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-cyan-500">
                <span class="text-[11px] text-slate-400">Default recommended: <code class="text-cyan-300">Admin@123456</code> (Note capital 'A')</span>
            </div>

            <button type="submit" name="reset_password" value="1" class="w-full py-2.5 px-4 bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-white font-semibold rounded-lg shadow-lg shadow-cyan-500/20 text-sm transition">
                ⚡ Set Super Admin Password Now
            </button>
        </form>

        <!-- Current Admin Users Table -->
        <div>
            <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-3">Accounts with Admin Privileges in Database</h3>
            <div class="overflow-x-auto border border-slate-800 rounded-xl">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-800 text-slate-300">
                        <tr>
                            <th class="p-3">ID</th>
                            <th class="p-3">Email</th>
                            <th class="p-3">Role</th>
                            <th class="p-3">SuperAdmin</th>
                            <th class="p-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800 text-slate-300">
                        <?php if (empty($adminUsers)): ?>
                            <tr><td colspan="5" class="p-3 text-center text-slate-500">No admin accounts found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($adminUsers as $u): ?>
                                <tr class="hover:bg-slate-800/40">
                                    <td class="p-3 font-mono"><?php echo $u['id']; ?></td>
                                    <td class="p-3 font-medium text-white"><?php echo htmlspecialchars($u['email']); ?></td>
                                    <td class="p-3"><?php echo htmlspecialchars($u['role'] ?? '—'); ?></td>
                                    <td class="p-3"><?php echo $u['is_super_admin'] ? '✅ Yes' : '❌ No'; ?></td>
                                    <td class="p-3"><span class="px-2 py-0.5 rounded text-[10px] <?php echo $u['status'] === 'active' ? 'bg-emerald-900/50 text-emerald-400' : 'bg-slate-800 text-slate-400'; ?>"><?php echo htmlspecialchars($u['status']); ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-6 flex justify-between items-center text-xs text-slate-500">
            <span>Doxa Business Finder Security</span>
            <a href="/login" class="text-cyan-400 hover:underline">Back to Login &rarr;</a>
        </div>
    </div>
</body>
</html>
