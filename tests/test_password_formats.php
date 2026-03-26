<?php
/**
 * Test that verify_password() handles all legacy hash formats.
 *
 * Covers: bcrypt, MD5, MD5(strtolower), MySQL PASSWORD(), SHA1, plain text
 */

require_once __DIR__ . '/../incs/security.inc.php';

echo "=== Password Format Verification Tests ===\n\n";
$pass = 0;
$fail = 0;

function test($label, $password, $storedHash, $expectValid) {
    global $pass, $fail;
    $result = verify_password($password, $storedHash);
    $valid = $result['valid'];
    $rehash = $result['needs_rehash'];

    if ($valid === $expectValid) {
        echo "[PASS] {$label}";
        if ($valid && $rehash) echo " (needs rehash)";
        echo "\n";
        $pass++;
    } else {
        echo "[FAIL] {$label} — expected " . ($expectValid ? 'valid' : 'invalid') . ", got " . ($valid ? 'valid' : 'invalid') . "\n";
        $fail++;
    }
}

// 1. bcrypt (modern)
$bcryptHash = password_hash('MyPassword123', PASSWORD_BCRYPT, ['cost' => 12]);
test("bcrypt — correct password", "MyPassword123", $bcryptHash, true);
test("bcrypt — wrong password", "WrongPassword", $bcryptHash, false);

// 2. MD5 (versions ~3.0-3.40)
$md5Hash = md5('admin');
test("MD5 — correct password", "admin", $md5Hash, true);
test("MD5 — wrong password", "wrong", $md5Hash, false);

// 3. MD5 with strtolower (some versions lowercased the password before hashing)
$md5LowerHash = md5(strtolower('Admin'));
test("MD5(strtolower) — correct case-insensitive", "Admin", $md5LowerHash, true);
test("MD5(strtolower) — correct lowercase", "admin", $md5LowerHash, true);

// 4. MySQL PASSWORD() — *SHA1(SHA1(password))
//    MySQL 4.1+ PASSWORD('admin') = '*4ACFE3202A5FF5CF467898FC58AAB1D615029441'
$mysqlHash = '*' . strtoupper(sha1(sha1('admin', true)));
test("MySQL PASSWORD() — correct password", "admin", $mysqlHash, true);
test("MySQL PASSWORD() — wrong password", "wrong", $mysqlHash, false);

// Verify the hash format matches what MySQL actually produces
echo "  (MySQL PASSWORD hash: {$mysqlHash})\n";

// 5. MySQL PASSWORD() with a real-world password
$mysqlHash2 = '*' . strtoupper(sha1(sha1('testing', true)));
test("MySQL PASSWORD('testing') — correct", "testing", $mysqlHash2, true);
test("MySQL PASSWORD('testing') — wrong", "Testing", $mysqlHash2, false);

// 6. SHA1 (some custom installs)
$sha1Hash = sha1('dispatcher');
test("SHA1 — correct password", "dispatcher", $sha1Hash, true);
test("SHA1 — wrong password", "wrong", $sha1Hash, false);

// 7. Plain text (very old or misconfigured)
test("Plain text — correct", "admin", "admin", true);
test("Plain text — wrong", "wrong", "admin", false);
test("Plain text — longer password", "MySecret", "MySecret", true);

// 8. Empty hash — always invalid
test("Empty hash — rejected", "admin", "", false);

// 9. All legacy formats should flag needs_rehash
echo "\n--- Rehash flags ---\n";
$formats = [
    'MD5' => md5('test'),
    'MySQL PASSWORD()' => '*' . strtoupper(sha1(sha1('test', true))),
    'SHA1' => sha1('test'),
    'Plain text' => 'test',
];
foreach ($formats as $name => $hash) {
    $result = verify_password('test', $hash);
    if ($result['valid'] && $result['needs_rehash']) {
        echo "[PASS] {$name} flags needs_rehash=true\n";
        $pass++;
    } else {
        echo "[FAIL] {$name} — valid=" . ($result['valid'] ? 'true' : 'false') . " needs_rehash=" . ($result['needs_rehash'] ? 'true' : 'false') . "\n";
        $fail++;
    }
}

// 10. Modern bcrypt should NOT need rehash (same cost)
$modernHash = password_hash('test', PASSWORD_BCRYPT, ['cost' => 12]);
$result = verify_password('test', $modernHash);
if ($result['valid'] && !$result['needs_rehash']) {
    echo "[PASS] bcrypt cost=12 does not need rehash\n";
    $pass++;
} else {
    echo "[FAIL] bcrypt cost=12 rehash flag wrong\n";
    $fail++;
}

echo "\n=== Results: {$pass} passed, {$fail} failed ===\n";
exit($fail > 0 ? 1 : 0);
