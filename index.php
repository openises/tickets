<?php
/*
 * ticketsCAD runtime entrypoint hardening notes:
 * - Removed installer/schema mutation logic from index bootstrap.
 * - Added centralized version/config gate via incs/versions.inc.php.
 * - Preserved runtime-only responsibilities (redirect/mobile/frame launch).
 */
error_reporting(E_ALL);
header('Cache-Control: no-cache, no-store, must-revalidate');

if (!file_exists('./incs/mysql.inc.php')) {
    header('Location: install.php');
    exit();
}

// Version gate — must run BEFORE functions.inc.php which loads login.inc.php
require_once './incs/versions.inc.php';

$versions = tickets_get_versions();
$version = $versions['installer'];
$installedVersion = $versions['installed'];

if ($installedVersion === null) {
    header('Location: install.php?reason=no_database');
    exit();
}

// Version mismatch — show upgrade notice with embedded admin login
if (!$versions['match'] && $installedVersion !== 'unknown (legacy)') {
    require './incs/mysql.inc.php';       // use require (not require_once) — versions.inc.php already loaded it in a function scope
    require_once './incs/security.inc.php';

    // Handle login POST
    $loginError = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upgrade_login'])) {
        $user = trim($_POST['username'] ?? '');
        $pass = $_POST['password'] ?? '';
        if ($user && $pass) {
            $mysqli = @new mysqli($mysql_host, $mysql_user, isset($mysql_passwd) ? $mysql_passwd : '', $mysql_db);
            if (!$mysqli->connect_errno) {
                $prefix = isset($mysql_prefix) ? $mysql_prefix : '';
                $stmt = $mysqli->prepare("SELECT id, user, passwd, level FROM `{$prefix}user` WHERE user = ? AND status = 'approved' LIMIT 1");
                $stmt->bind_param('s', $user);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result ? $result->fetch_assoc() : null;
                $stmt->close();
                if ($row && (int)$row['level'] <= 1) {
                    $verified = false;
                    if (function_exists('password_verify') && password_verify($pass, $row['passwd'])) {
                        $verified = true;
                    } elseif (md5($pass) === $row['passwd']) {
                        $verified = true;
                    }
                    if ($verified) {
                        session_start();
                        session_regenerate_id(true);
                        $_SESSION['user_id'] = $row['id'];
                        $_SESSION['level'] = (int)$row['level'];
                        $_SESSION['user'] = $row['user'];
                        $mysqli->close();
                        header('Location: install.php');
                        exit();
                    }
                }
                $mysqli->close();
            }
        }
        $loginError = 'Invalid credentials or insufficient privileges. Admin account required.';
    }

    $iv = htmlspecialchars($installedVersion, ENT_QUOTES, 'UTF-8');
    $cv = htmlspecialchars($version, ENT_QUOTES, 'UTF-8');
    ?><!doctype html>
<html><head>
<meta charset="utf-8">
<title>ticketsCAD — Update Required</title>
<style>
body{background:#1a1a2e;color:#e0e0e0;font-family:Arial,sans-serif;margin:0;display:flex;justify-content:center;align-items:center;min-height:100vh}
.card{background:#16213e;border:1px solid #0f3460;border-radius:12px;padding:32px 40px;max-width:520px;width:100%;box-shadow:0 8px 32px rgba(0,0,0,.4);text-align:center}
h1{margin:0 0 8px;font-size:24px;color:#e94560}
.versions{margin:20px 0;padding:16px;background:#0f3460;border-radius:8px;display:flex;justify-content:center;gap:32px}
.ver-label{font-size:12px;color:#8899aa;text-transform:uppercase;letter-spacing:1px}
.ver-value{font-size:20px;font-weight:bold;margin-top:4px}
.old{color:#ffc107}.new{color:#4ecca3}
.arrow{font-size:28px;color:#e94560;align-self:center}
p{color:#8899aa;line-height:1.6;margin:16px 0}
.login-form{margin-top:20px;padding-top:20px;border-top:1px solid #0f3460}
.login-form input{width:100%;padding:10px 12px;border:1px solid #0f3460;border-radius:8px;background:#0d1b3e;color:#e0e0e0;font-size:14px;margin-bottom:10px;box-sizing:border-box}
.login-form input::placeholder{color:#556}
.login-form input:focus{outline:none;border-color:#1570ef}
.btn{display:inline-block;background:#e94560;color:#fff;border:none;padding:12px 28px;border-radius:8px;font-weight:bold;font-size:16px;cursor:pointer;transition:background .2s;width:100%}
.btn:hover{background:#c73652}
.error{color:#e94560;font-size:13px;margin-bottom:10px}
.hint{font-size:12px;color:#667;margin-top:16px}
</style>
</head><body>
<div class="card">
    <h1>Database Update Required</h1>
    <p>The application files have been updated but your database has not been upgraded yet.</p>
    <div class="versions">
        <div><div class="ver-label">Database</div><div class="ver-value old"><?php echo $iv; ?></div></div>
        <div class="arrow">&#8594;</div>
        <div><div class="ver-label">Application</div><div class="ver-value new"><?php echo $cv; ?></div></div>
    </div>
    <p>Log in as an administrator to run the upgrade.</p>
    <form class="login-form" method="post" action="index.php">
        <input type="hidden" name="upgrade_login" value="1">
        <?php if ($loginError) { ?><div class="error"><?php echo htmlspecialchars($loginError, ENT_QUOTES, 'UTF-8'); ?></div><?php } ?>
        <input type="text" name="username" placeholder="Admin username" required autofocus>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit" class="btn">Log In &amp; Upgrade</button>
    </form>
    <div class="hint">Only administrator accounts (level 0 or 1) can run upgrades.</div>
</div>
</body></html><?php
    exit();
}

require_once './incs/functions.inc.php';
$dispVersion = ($installedVersion !== null && $installedVersion !== '') ? $installedVersion : $version;

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
            if (safe_strlen($match) > 0 && !stristr($text, 'MSIE')) {
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
