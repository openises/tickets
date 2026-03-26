<?php
/**
 * Emergency Admin Password Reset
 *
 * Usage: Place this file in your tickets/tools/ directory and run it in your browser:
 *   http://your-server/tickets/tools/reset-admin-password.php
 *
 * Or via PHP CLI:
 *   php tools/reset-admin-password.php
 *
 * This will:
 *   1. Show all admin accounts (level 0 or 1)
 *   2. Let you reset the password for any admin account
 *   3. Rehash from MD5 to bcrypt if needed
 *
 * IMPORTANT: Delete this file after use for security!
 */

// Detect CLI vs web
$isCli = (php_sapi_name() === 'cli');

// Load database connection
$configFile = __DIR__ . '/../incs/mysql.inc.php';
if (!file_exists($configFile)) {
    $msg = "ERROR: Cannot find database config at: $configFile\n";
    $msg .= "Make sure this file is in the tickets/tools/ directory.\n";
    if ($isCli) { echo $msg; } else { echo "<pre>$msg</pre>"; }
    exit(1);
}

require_once $configFile;

// Connect to database
try {
    $dsn = "mysql:host={$mysql_host};dbname={$mysql_db};charset=utf8mb4";
    $pdo = new PDO($dsn, $mysql_user, $mysql_passwd, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    $msg = "DATABASE ERROR: " . $e->getMessage() . "\n";
    if ($isCli) { echo $msg; } else { echo "<pre>$msg</pre>"; }
    exit(1);
}

$prefix = isset($mysql_prefix) ? $mysql_prefix : '';

// Handle form submission (web) or interactive (CLI)
$message = '';
$success = false;

if (!$isCli && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'], $_POST['new_password'])) {
    $userId = (int) $_POST['user_id'];
    $newPass = $_POST['new_password'];

    if (strlen($newPass) < 4) {
        $message = 'Password must be at least 4 characters.';
    } else {
        // Determine which column holds the password
        $passCol = 'passwd';
        try {
            $cols = $pdo->query("SHOW COLUMNS FROM `{$prefix}user`")->fetchAll();
            $colNames = array_column($cols, 'Field');
            if (in_array('pass', $colNames) && !in_array('passwd', $colNames)) {
                $passCol = 'pass';
            }
        } catch (Exception $e) {}

        $hash = password_hash($newPass, PASSWORD_BCRYPT, ['cost' => 12]);
        $stmt = $pdo->prepare("UPDATE `{$prefix}user` SET `{$passCol}` = ? WHERE `id` = ?");
        $stmt->execute([$hash, $userId]);

        if ($stmt->rowCount() > 0) {
            $message = "Password updated successfully for user ID $userId! You can now log in.";
            $success = true;
        } else {
            $message = "No rows updated. Check that user ID $userId exists.";
        }
    }
}

// Get admin users
$passCol = 'passwd';
try {
    $cols = $pdo->query("SHOW COLUMNS FROM `{$prefix}user`")->fetchAll();
    $colNames = array_column($cols, 'Field');
    if (in_array('pass', $colNames) && !in_array('passwd', $colNames)) {
        $passCol = 'pass';
    }
} catch (Exception $e) {}

try {
    $stmt = $pdo->query("SELECT `id`, `user`, `name`, `level`, `{$passCol}` AS pass_hash FROM `{$prefix}user` ORDER BY `level` ASC, `user` ASC");
    $users = $stmt->fetchAll();
} catch (Exception $e) {
    $users = [];
    $message = "Error reading users: " . $e->getMessage();
}

// Identify hash type
function detect_hash_type($hash) {
    if (empty($hash)) return 'EMPTY';
    if (strpos($hash, '$2y$') === 0 || strpos($hash, '$2a$') === 0 || strpos($hash, '$2b$') === 0) return 'bcrypt';
    if (strlen($hash) === 32 && ctype_xdigit($hash)) return 'MD5';
    if (strlen($hash) === 40 && ctype_xdigit($hash)) return 'SHA1';
    return 'unknown (' . strlen($hash) . ' chars)';
}

function level_name($level) {
    $names = [0 => 'Super Admin', 1 => 'Admin', 2 => 'Dispatcher', 3 => 'Read-Only', 4 => 'Unit', 5 => 'Stats'];
    return isset($names[$level]) ? $names[$level] : "Level $level";
}

// CLI mode
if ($isCli) {
    echo "=== TicketsCAD Admin Password Reset ===\n\n";

    if (empty($users)) {
        echo "No users found in the database.\n";
        exit(1);
    }

    echo "Users found:\n";
    echo str_pad('ID', 5) . str_pad('Username', 20) . str_pad('Level', 20) . str_pad('Hash Type', 15) . "\n";
    echo str_repeat('-', 60) . "\n";
    foreach ($users as $u) {
        echo str_pad($u['id'], 5) . str_pad($u['user'], 20) . str_pad(level_name((int)$u['level']), 20) . str_pad(detect_hash_type($u['pass_hash']), 15) . "\n";
    }

    echo "\nTo reset a password, run:\n";
    echo "  php tools/reset-admin-password.php <user_id> <new_password>\n\n";

    // Check if arguments provided
    if (isset($argv[1]) && isset($argv[2])) {
        $userId = (int) $argv[1];
        $newPass = $argv[2];
        $hash = password_hash($newPass, PASSWORD_BCRYPT, ['cost' => 12]);
        $stmt = $pdo->prepare("UPDATE `{$prefix}user` SET `{$passCol}` = ? WHERE `id` = ?");
        $stmt->execute([$hash, $userId]);
        if ($stmt->rowCount() > 0) {
            echo "Password updated for user ID $userId.\n";
            echo "New hash type: bcrypt\n";
            echo "\nIMPORTANT: Delete this file after use!\n";
        } else {
            echo "ERROR: No rows updated for user ID $userId.\n";
        }
    }
    exit(0);
}

// Web mode
?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>TicketsCAD - Admin Password Reset</title>
    <style>
        body { font-family: Arial, sans-serif; background: #1a1a2e; color: #e0e0e0; margin: 0; padding: 20px; }
        .container { max-width: 700px; margin: 0 auto; }
        h1 { color: #e94560; font-size: 24px; }
        .card { background: #16213e; border: 1px solid #0f3460; border-radius: 8px; padding: 20px; margin: 16px 0; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 8px 12px; text-align: left; border-bottom: 1px solid #0f3460; }
        th { color: #8899aa; font-size: 12px; text-transform: uppercase; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; }
        .badge-admin { background: #e9456020; color: #e94560; }
        .badge-bcrypt { background: #19875420; color: #198754; }
        .badge-md5 { background: #ffc10720; color: #ffc107; }
        .badge-empty { background: #dc354520; color: #dc3545; }
        input[type="password"], input[type="text"] { background: #1a1a2e; border: 1px solid #0f3460; color: #e0e0e0; padding: 8px 12px; border-radius: 4px; width: 200px; }
        button { background: #1570ef; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; font-weight: bold; }
        button:hover { background: #1259c4; }
        .success { background: #19875420; border: 1px solid #198754; color: #198754; padding: 12px; border-radius: 8px; }
        .error { background: #dc354520; border: 1px solid #dc3545; color: #dc3545; padding: 12px; border-radius: 8px; }
        .warning { background: #ffc10720; border: 1px solid #ffc107; color: #ffc107; padding: 12px; border-radius: 8px; margin-top: 16px; }
    </style>
</head>
<body>
<div class="container">
    <h1>TicketsCAD — Admin Password Reset</h1>

    <?php if ($message): ?>
        <div class="<?php echo $success ? 'success' : 'error'; ?>"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <div class="card">
        <h3>User Accounts</h3>
        <?php if (empty($users)): ?>
            <p>No users found in the database.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr><th>ID</th><th>Username</th><th>Name</th><th>Level</th><th>Hash</th><th>Reset</th></tr>
                </thead>
                <tbody>
                <?php foreach ($users as $u):
                    $hashType = detect_hash_type($u['pass_hash']);
                    $badgeClass = ($hashType === 'bcrypt') ? 'badge-bcrypt' : (($hashType === 'MD5') ? 'badge-md5' : 'badge-empty');
                ?>
                    <tr>
                        <td><?php echo (int)$u['id']; ?></td>
                        <td><strong><?php echo htmlspecialchars($u['user']); ?></strong></td>
                        <td><?php echo htmlspecialchars($u['name'] ?? ''); ?></td>
                        <td><span class="badge badge-admin"><?php echo level_name((int)$u['level']); ?></span></td>
                        <td><span class="badge <?php echo $badgeClass; ?>"><?php echo $hashType; ?></span></td>
                        <td>
                            <form method="POST" style="display:flex;gap:4px;align-items:center">
                                <input type="hidden" name="user_id" value="<?php echo (int)$u['id']; ?>">
                                <input type="password" name="new_password" placeholder="New password" required minlength="4">
                                <button type="submit">Reset</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <div class="warning">
        <strong>Security Notice:</strong> Delete this file immediately after resetting your password!<br>
        Path: <code>tickets/tools/reset-admin-password.php</code>
    </div>
</div>
</body>
</html>
