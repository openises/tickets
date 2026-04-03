<?php
/**
 * Member Photo Proxy
 *
 * Serves member ID photos from the mdb_pictures directory, which is
 * blocked by .htaccess (deny from all) for security. This script
 * validates the member ID and serves the image directly.
 *
 * Authentication: The page embedding this <IMG> tag already requires
 * login. This script only serves .jpg files from a fixed path pattern
 * (mdb_pictures/{int}/id.jpg) so there is no directory traversal risk.
 *
 * Usage: ajax/get_member_photo.php?id=123
 *
 * 3/14/2026 - New file
 */

// Suppress any PHP warnings/notices that would corrupt image output
error_reporting(0);

if (empty($_GET['id'])) {
    http_response_code(400);
    exit;
}

$member_id = intval($_GET['id']);
if ($member_id <= 0) {
    http_response_code(400);
    exit;
}

// Fixed path pattern — no user-controlled path components beyond the integer ID
$file = dirname(__DIR__) . "/mdb_pictures/" . $member_id . "/id.jpg";

if (!file_exists($file) || !is_readable($file)) {
    // Serve the no_image placeholder
    $file = dirname(__DIR__) . "/images/no_image.jpg";
    if (!file_exists($file)) {
        http_response_code(404);
        exit;
    }
}

// Clean any output buffers that may have been started
while (ob_get_level()) {
    ob_end_clean();
}

header('Content-Type: image/jpeg');
header('Content-Length: ' . filesize($file));
header('Cache-Control: private, max-age=300');
readfile($file);
exit;
