<?php
/*
 * ticketsCAD runtime entrypoint hardening notes:
 * - Removed installer/schema mutation logic from index bootstrap.
 * - Added centralized version/config gate via incs/versions.inc.php.
 * - Preserved runtime-only responsibilities (redirect/mobile/frame launch).
 */
error_reporting(E_ALL);

if (!file_exists('./incs/mysql.inc.php')) {
    header('Location: install.php');
    exit();
}

require_once './incs/versions.inc.php';
require_once './incs/functions.inc.php';

$versions = tickets_get_versions();
$version = $versions['installer'];
$installedVersion = $versions['installed'];
$dispVersion = ($installedVersion !== null && $installedVersion !== '') ? $installedVersion : $version;

if ($installedVersion === null) {
    header('Location: install.php');
    exit();
}

// cache buster
$noforward_string = '';

// Mobile redirect
if ((!isset($_POST) || !array_key_exists('noautoforward', $_POST)) &&
    ((!isset($_SESSION)) || (array_key_exists('noautoforward', $_SESSION) && ($_SESSION['noautoforward'] == false)))) {
    if (get_variable('use_responder_mobile') == '1') {
        $text = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        $agents = array(
            'Mozilla/4.', 'Mozilla/3.0', 'AvantGo', 'ProxiNet', 'Danger hiptop 1.0', 'DoCoMo/',
            'Google CHTML Proxy/', 'UP.Browser/', 'SEMC-Browser/', 'J-PHONE/', 'PDXGW/', 'ASTEL/',
            'Mozilla/1.22', 'Handspring', 'Windows CE', 'PPC', 'Mozilla/2.0', 'Blazer/', 'Palm',
            'WebPro/', 'EPOC32-WTL/', 'Tungsten', 'Netfront/', 'Mobile Content Viewer/', 'PDA', 'MMP/2.0',
            'Embedix/', 'Qtopia/', 'Xiino/', 'BlackBerry', 'Gecko/20031007', 'MOT-', 'UP.Link/',
            'Smartphone', 'portalmmm/', 'Nokia', 'Symbian', 'AppleWebKit/413', 'UPG1 UP/', 'RegKing',
            'STNC-WTL/', 'J2ME', 'Opera Mini/', 'SEC-', 'ReqwirelessWeb/', 'AU-MIC/', 'Sharp', 'SIE-',
            'SonyEricsson', 'Elaine/', 'SAMSUNG-', 'Panasonic', 'Siemens', 'Sony', 'Verizon', 'Cingular',
            'Sprint', 'AT&T;', 'Nextel', 'Pocket PC', 'T-Mobile', 'Orange', 'Casio', 'HTC', 'Motorola',
            'Samsung', 'NEC', 'Mobi'
        );

        $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
        $uri = isset($_SERVER['PHP_SELF']) ? rtrim(dirname($_SERVER['PHP_SELF']), '/\\') : '';
        $url = 'http://' . $host . $uri . '/rm/index.php';

        foreach ($agents as $agent) {
            $match = stristr($text, $agent);
            if (strlen($match) > 0 && !stristr($text, 'MSIE')) {
                echo '<meta http-equiv="refresh" content="0;URL=' . $url . '">';
                exit();
            }
        }
    }
} else {
    $noforward_string = '&noaf=1';
}

$buster = isset($_POST['logout']) ? (strval(rand()) . '&logout=1') : strval(rand());
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
    <meta name="ROBOTS" content="INDEX,FOLLOW" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta http-equiv="Expires" content="0" />
    <meta http-equiv="Cache-Control" content="NO-CACHE" />
    <meta http-equiv="Pragma" content="NO-CACHE" />
    <meta http-equiv="expires" content="Wed, 26 Feb 1997 08:21:57 GMT" />
    <meta http-equiv="Content-Script-Type" content="application/x-javascript" />
    <title>Tickets <?php print $dispVersion; ?></title>
</head>
<?php if (get_variable('call_board') == 2) { ?>
<frameset id="the_frames" rows="<?php print (get_variable('framesize') + 25); ?>, 0,*" border="<?php print get_variable('frameborder'); ?>" bordercolor="#ff0000">
    <frame src="top.php?stuff=<?php print $buster; ?>" name="upper" scrolling="no" />
    <frame src="board.php?stuff=<?php print $buster; ?>" id="what" name="calls" scrolling="AUTO" />
    <frame src="main.php?stuff=<?php print $buster; ?><?php print $noforward_string; ?>" name="main" />
<?php } else { ?>
<frameset id="the_frames" rows="<?php print (get_variable('framesize') + 25); ?>, *" border="<?php print get_variable('frameborder'); ?>">
    <frame src="top.php?stuff=<?php print $buster; ?>" name="upper" scrolling="no" />
    <frame src="main.php?stuff=<?php print $buster; ?><?php print $noforward_string; ?>" name="main" />
<?php } ?>
    <noframes>
        <body>Tickets requires a frames-capable browser.</body>
    </noframes>
</frameset>
</html>
