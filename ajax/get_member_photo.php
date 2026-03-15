<?php
/**
 * Member Photo Proxy
 *
 * Serves member ID photos from the mdb_pictures directory, which is
 * blocked by .htaccess (deny from all) for security. This script
 * checks authentication before serving the image.
 *
 * Usage: ajax/get_member_photo.php?id=123
 *
 * 3/14/2026 - New file
 */
require_once('../incs/functions.inc.php');

if (empty($_GET['id'])) {
	http_response_code(400);
	exit;
}

$member_id = intval($_GET['id']);
if ($member_id <= 0) {
	http_response_code(400);
	exit;
}

$file = "../mdb_pictures/" . $member_id . "/id.jpg";

if (!file_exists($file) || !is_readable($file)) {
	// Serve the no_image placeholder
	$file = "../images/no_image.jpg";
	if (!file_exists($file)) {
		http_response_code(404);
		exit;
	}
}

header('Content-Type: image/jpeg');
header('Content-Length: ' . filesize($file));
header('Cache-Control: private, max-age=300');
readfile($file);
exit;
