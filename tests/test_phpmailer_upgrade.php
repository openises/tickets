<?php
// Test that PHPMailer 6.12 loads correctly
require_once __DIR__ . '/../lib/phpmailer/autoload.php';

$pass = 0;
$fail = 0;

// Test 1: Classes exist
if (class_exists('PHPMailer\PHPMailer\PHPMailer')) { $pass++; } else { $fail++; echo "FAIL: PHPMailer class not found\n"; }
if (class_exists('PHPMailer\PHPMailer\SMTP')) { $pass++; } else { $fail++; echo "FAIL: SMTP class not found\n"; }
if (class_exists('PHPMailer\PHPMailer\Exception')) { $pass++; } else { $fail++; echo "FAIL: Exception class not found\n"; }

// Test 2: Can instantiate
$mail = new PHPMailer\PHPMailer\PHPMailer(true);
if ($mail instanceof PHPMailer\PHPMailer\PHPMailer) { $pass++; } else { $fail++; echo "FAIL: Cannot instantiate PHPMailer\n"; }

// Test 3: Basic configuration works
$mail->isSMTP();
$mail->Host = 'localhost';
$mail->Port = 25;
$mail->isHTML(true);
$mail->Subject = 'Test';
$mail->Body = '<b>Test</b>';
$mail->addAddress('test@example.com');
$pass++;

// Test 4: Version is 6.9+
$version = PHPMailer\PHPMailer\PHPMailer::VERSION;
if (version_compare($version, '6.9.0', '>=')) {
    $pass++;
} else {
    $fail++;
    echo "FAIL: Expected 6.9+, got $version\n";
}

// Test 5: Old 5.x files are gone
$oldFiles = array(
    __DIR__ . '/../lib/phpmailer/class.phpmailer.php',
    __DIR__ . '/../lib/phpmailer/class.smtp.php',
    __DIR__ . '/../lib/phpmailer/class.pop3.php',
    __DIR__ . '/../lib/phpmailer/PHPMailerAutoload.php',
    __DIR__ . '/../lib/phpmailer/ntlm_sasl_client.php',
);
$oldFilesGone = true;
foreach ($oldFiles as $f) {
    if (file_exists($f)) {
        $oldFilesGone = false;
        echo "FAIL: Old file still exists: $f\n";
    }
}
if ($oldFilesGone) { $pass++; } else { $fail++; }

// Test 6: SwiftMailer is gone
if (!is_dir(__DIR__ . '/../lib/classes/Swift')) {
    $pass++;
} else {
    $fail++;
    echo "FAIL: SwiftMailer directory still exists\n";
}

// Test 7: functions_mail.php (dead code) is gone
if (!file_exists(__DIR__ . '/../incs/functions_mail.php')) {
    $pass++;
} else {
    $fail++;
    echo "FAIL: functions_mail.php still exists\n";
}

echo "\nPHPMailer upgrade tests: $pass passed, $fail failed. Version: $version\n";
exit($fail > 0 ? 1 : 0);
