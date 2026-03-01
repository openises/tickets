<?php
/*
 * ticketsCAD installer hardening notes:
 * - Installer owns install/upgrade/schema operations; index.php no longer mutates schema.
 * - Supports clean install, upgrade sync, and write-config modes.
 * - Streams step-by-step progress and records installed _version for parity checks.
 */
error_reporting(E_ALL);
if (function_exists('mysqli_report')) { mysqli_report(MYSQLI_REPORT_OFF); }

require_once __DIR__ . '/incs/versions.inc.php';
require_once __DIR__ . '/incs/install_schema.inc.php';

function h($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

function emit_line($line) {
    echo $line . "
";
    if (function_exists('ob_flush')) { @ob_flush(); }
    flush();
}

function load_mysql_defaults() {
    $defaults = array('host' => 'localhost', 'user' => '', 'pass' => '', 'db' => '', 'prefix' => '');
    $inc = __DIR__ . '/incs/mysql.inc.php';
    if (is_readable($inc)) {
        @include $inc;
        if (isset($mysql_host)) { $defaults['host'] = $mysql_host; }
        if (isset($mysql_user)) { $defaults['user'] = $mysql_user; }
        if (isset($mysql_passwd)) { $defaults['pass'] = $mysql_passwd; }
        if (isset($mysql_db)) { $defaults['db'] = $mysql_db; }
        if (isset($mysql_prefix)) { $defaults['prefix'] = $mysql_prefix; }
    }
    return $defaults;
}

function connect_db($cfg) {
    $mysqli = @new mysqli($cfg['host'], $cfg['user'], $cfg['pass'], $cfg['db']);
    if ($mysqli->connect_errno) { return null; }
    $mysqli->set_charset('utf8mb4');
    return $mysqli;
}

function table_exists_mysqli($mysqli, $table) {
    $esc = $mysqli->real_escape_string($table);
    $res = $mysqli->query("SHOW TABLES LIKE '{$esc}'");
    $ok = ($res && $res->num_rows > 0);
    if ($res) { $res->free(); }
    return $ok;
}

function detect_install($cfg) {
    $out = array('exists' => false, 'installed_version' => null, 'db_version' => null, 'legacy' => false);
    $mysqli = connect_db($cfg);
    if (!$mysqli) { return $out; }

    $out['db_version'] = $mysqli->server_info;
    $settings = $cfg['prefix'] . 'settings';
    if (table_exists_mysqli($mysqli, $settings)) {
        $out['exists'] = true;
        $res = $mysqli->query("SELECT `value` FROM `{$settings}` WHERE `name` = '_version' LIMIT 1");
        if ($res && ($row = $res->fetch_assoc()) && trim($row['value']) !== '') {
            $out['installed_version'] = trim($row['value']);
        } else {
            $out['installed_version'] = 'unknown (legacy)';
            $out['legacy'] = true;
        }
        if ($res) { $res->free(); }
    }

    $mysqli->close();
    return $out;
}

function write_mysql_config($cfg) {
    $path = __DIR__ . '/incs/mysql.inc.php';
    $body = "<?php\n";
    $body .= '$mysql_host = ' . var_export($cfg['host'], true) . ";\n";
    $body .= '$mysql_user = ' . var_export($cfg['user'], true) . ";\n";
    $body .= '$mysql_passwd = ' . var_export($cfg['pass'], true) . ";\n";
    $body .= '$mysql_db = ' . var_export($cfg['db'], true) . ";\n";
    $body .= '$mysql_prefix = ' . var_export($cfg['prefix'], true) . ";\n";
    return file_put_contents($path, $body) !== false;
}

function apply_prefix($sql, $prefix) {
    $patterns = array(
        '/(CREATE TABLE\s+`)([^`]+)(`)/i',
        '/(INSERT INTO\s+`)([^`]+)(`)/i',
        '/(ALTER TABLE\s+`)([^`]+)(`)/i',
        '/(DROP TABLE IF EXISTS\s+`)([^`]+)(`)/i',
        '/(UPDATE\s+`)([^`]+)(`)/i',
        '/(DELETE FROM\s+`)([^`]+)(`)/i'
    );

    foreach ($patterns as $pattern) {
        $sql = preg_replace_callback($pattern, function($m) use ($prefix) {
            if ($prefix === '' || strpos($m[2], $prefix) === 0) {
                return $m[1] . $m[2] . $m[3];
            }
            return $m[1] . $prefix . $m[2] . $m[3];
        }, $sql);
    }

    return $sql;
}

function create_or_sync_table($mysqli, $tableName, $createSql, $mode, &$logs) {
    if ($mode === 'upgrade' && table_exists_mysqli($mysqli, $tableName)) {
        if (preg_match('/CREATE TABLE\s+`[^`]+`\s*\((.*)\)\s*ENGINE/is', $createSql, $m)) {
            $lines = preg_split('/,\n/', $m[1]);
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '' || $line[0] !== '`') { continue; }
                if (!preg_match('/^`([^`]+)`\s+(.+)$/', $line, $cm)) { continue; }
                $column = $cm[1];
                $def = "`{$column}` {$cm[2]}";
                $colEsc = $mysqli->real_escape_string($column);
                $hasRes = $mysqli->query("SHOW COLUMNS FROM `{$tableName}` LIKE '{$colEsc}'");
                $has = ($hasRes && $hasRes->num_rows > 0);
                if ($hasRes) { $hasRes->free(); }
                $alter = $has ? "ALTER TABLE `{$tableName}` MODIFY COLUMN {$def}" : "ALTER TABLE `{$tableName}` ADD COLUMN {$def}";
                if (!$mysqli->query($alter)) {
                    $logs[] = "Schema sync warning on {$tableName}.{$column}: " . $mysqli->error;
                }
            }
        }
        return;
    }

    if (!$mysqli->query($createSql)) {
        $logs[] = "Create table warning for {$tableName}: " . $mysqli->error;
    }
}

function upsert_version_setting($mysqli, $prefix, $version) {
    $table = $prefix . 'settings';
    if (!table_exists_mysqli($mysqli, $table)) { return; }
    $stmt = $mysqli->prepare("UPDATE `{$table}` SET `value` = ? WHERE `name` = '_version'");
    if ($stmt) {
        $stmt->bind_param('s', $version);
        $stmt->execute();
        $affected = $stmt->affected_rows;
        $stmt->close();
        if ($affected > 0) { return; }
    }
    $stmt = $mysqli->prepare("INSERT INTO `{$table}` (`name`,`value`) VALUES ('_version', ?) ");
    if ($stmt) {
        $stmt->bind_param('s', $version);
        $stmt->execute();
        $stmt->close();
    }
}

function ensure_admin_user($mysqli, $prefix, $username, $password, $displayName) {
    $table = $prefix . 'user';
    if (!table_exists_mysqli($mysqli, $table)) { return false; }
    $hash = password_hash($password, PASSWORD_BCRYPT);
    $u = $mysqli->real_escape_string($username);
    $d = $mysqli->real_escape_string($displayName);
    $h = $mysqli->real_escape_string($hash);

    $check = $mysqli->query("SELECT `id` FROM `{$table}` WHERE `user`='{$u}' LIMIT 1");
    if ($check && $check->num_rows > 0) {
        $row = $check->fetch_assoc();
        $id = (int)$row['id'];
        $check->free();
        return (bool)$mysqli->query("UPDATE `{$table}` SET `passwd`='{$h}', `info`='{$d}', `level`=1, `status`='approved' WHERE `id`={$id}");
    }
    if ($check) { $check->free(); }
    return (bool)$mysqli->query("INSERT INTO `{$table}` (`user`,`passwd`,`info`,`level`,`status`,`open_at`,`sort_desc`,`reporting`) VALUES ('{$u}','{$h}','{$d}',1,'approved','d',1,0)");
}

function perform_install($cfg, $mode, $adminUser, $adminPass, $adminName, $installerVersion, $emit = null) {
    global $INSTALL_SCHEMA_TABLES, $INSTALL_SCHEMA_SEED;

    $logs = array();
    $push = function($msg) use (&$logs, $emit) {
        $logs[] = $msg;
        if ($emit) { call_user_func($emit, $msg); }
    };
    $mysqli = connect_db($cfg);
    if (!$mysqli) {
        return array(false, array('Database connection failed. Check MySQL credentials.'));
    }

    if ($mode === 'write_config') {
        $push('Writing configuration file...');
        $ok = write_mysql_config($cfg);
        $mysqli->close();
        return array($ok, array($ok ? 'Config file written to incs/mysql.inc.php.' : 'Failed to write incs/mysql.inc.php.'));
    }

    if ($mode === 'install_clean') {
        $push('Clean install selected: dropping existing tables...');
        $tables = $mysqli->query('SHOW TABLES');
        if ($tables) {
            while ($row = $tables->fetch_array(MYSQLI_NUM)) {
                if (!$mysqli->query("DROP TABLE IF EXISTS `{$row[0]}`")) {
                    $push('Drop warning: ' . $mysqli->error);
                }
            }
            $tables->free();
        }
    }

    foreach ($INSTALL_SCHEMA_TABLES as $baseName => $createBase) {
        $push('Applying table schema: ' . $baseName . ' ...');
        $tableName = $cfg['prefix'] . $baseName;
        $createSql = apply_prefix($createBase, $cfg['prefix']);
        create_or_sync_table($mysqli, $tableName, $createSql, $mode, $logs);
    }

    foreach ($INSTALL_SCHEMA_SEED as $insertBase) {
        if (preg_match('/^INSERT INTO\s+`user`/i', $insertBase)) {
            continue;
        }
        if (preg_match('/^INSERT INTO\s+`([^`]+)`/i', $insertBase, $mSeed)) { $push('Seeding data: ' . $mSeed[1] . ' ...'); }
        $insertSql = apply_prefix($insertBase, $cfg['prefix']);
        if (!$mysqli->query($insertSql)) {
            $push('Seed warning: ' . $mysqli->error);
        }
    }

    if (!ensure_admin_user($mysqli, $cfg['prefix'], $adminUser, $adminPass, $adminName)) {
        $push('Warning: failed to create/update admin account.');
    } else {
        $push('Admin account provisioned.');
    }

    $push('Updating installed version setting...');
    upsert_version_setting($mysqli, $cfg['prefix'], $installerVersion);
    $push('Writing mysql config file...');
    write_mysql_config($cfg);
    $mysqli->close();

    $push('Installer version recorded as ' . $installerVersion . '.');
    return array(true, $logs);
}

$versions = tickets_get_versions();
$installerVersion = $versions['installer'];
$defaults = load_mysql_defaults();
$detection = detect_install($defaults);

session_start();
if ($detection['exists']) {
    $isAdmin = isset($_SESSION['level']) && ((int)$_SESSION['level'] === 0 || (int)$_SESSION['level'] === 1);
    if (!$isAdmin) {
        header('Location: ./incs/login.inc.php');
        exit();
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'execute_stream') {
    @set_time_limit(0);
    while (ob_get_level() > 0) { @ob_end_flush(); }
    header('Content-Type: text/plain; charset=UTF-8');
    header('Cache-Control: no-cache');

    $cfg = array(
        'host' => trim($_POST['db_host']),
        'user' => trim($_POST['db_user']),
        'pass' => (string)$_POST['db_pass'],
        'db' => trim($_POST['db_name']),
        'prefix' => trim($_POST['db_prefix'])
    );

    $mode = isset($_POST['mode']) ? $_POST['mode'] : 'install_clean';
    $adminUser = trim((string)$_POST['admin_user']);
    $adminPass = (string)$_POST['admin_pass'];
    $adminName = trim((string)$_POST['admin_name']);

    if ($mode !== 'write_config' && ($adminUser === '' || strlen($adminPass) < 6 || $adminName === '')) {
        emit_line('ERROR: Admin user, name, and password (min 6 chars) are required.');
        emit_line('DONE:0');
        exit();
    }

    emit_line('Starting installer...');
    list($ok, $logs) = perform_install($cfg, $mode, $adminUser, $adminPass, $adminName, $installerVersion, 'emit_line');
    emit_line('DONE:' . ($ok ? '1' : '0'));
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'execute') {
    header('Content-Type: application/json');
    $cfg = array(
        'host' => trim($_POST['db_host']),
        'user' => trim($_POST['db_user']),
        'pass' => (string)$_POST['db_pass'],
        'db' => trim($_POST['db_name']),
        'prefix' => trim($_POST['db_prefix'])
    );

    $mode = isset($_POST['mode']) ? $_POST['mode'] : 'install_clean';
    $adminUser = trim((string)$_POST['admin_user']);
    $adminPass = (string)$_POST['admin_pass'];
    $adminName = trim((string)$_POST['admin_name']);

    if ($mode !== 'write_config') {
        if ($adminUser === '' || strlen($adminPass) < 6 || $adminName === '') {
            echo json_encode(array('ok' => false, 'logs' => array('Admin user, name, and password (min 6 chars) are required.')));
            exit();
        }
    }

    list($ok, $logs) = perform_install($cfg, $mode, $adminUser, $adminPass, $adminName, $installerVersion);
    echo json_encode(array('ok' => $ok, 'logs' => $logs));
    exit();
}

$serverSoftware = isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : 'unknown';
$modeDefault = $detection['exists'] ? 'upgrade' : 'install_clean';
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>ticketsCAD Installer</title>
<link rel="stylesheet" href="default.css" type="text/css">
<style>
body{background:#f5f7fb;color:#1d2939;font-family:Arial,sans-serif;margin:0}
.wrap{max-width:980px;margin:28px auto;padding:0 16px}
.card{background:#fff;border:1px solid #d0d5dd;border-radius:12px;padding:18px 20px;box-shadow:0 4px 12px rgba(16,24,40,.08)}
h1{margin:0 0 8px;font-size:28px}.muted{color:#475467}
.grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}
label{font-weight:bold;display:block;margin-bottom:4px} input,select{width:100%;padding:9px;border:1px solid #98a2b3;border-radius:8px}
.mode-switch{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-top:4px}
.mode-option{position:relative;display:block;border:1px solid #d0d5dd;border-radius:10px;padding:10px 12px;background:#fff;cursor:pointer;transition:.2s}
.mode-option input{position:absolute;opacity:0;pointer-events:none}
.mode-title{display:block;font-weight:700;color:#101828;font-size:13px}
.mode-desc{display:block;color:#667085;font-size:12px;margin-top:4px;line-height:1.3}
.mode-option.active{border-color:#1570ef;background:#eef4ff;box-shadow:inset 0 0 0 1px #1570ef}
.mode-option.disabled{opacity:.55;cursor:not-allowed;background:#f9fafb}
.badge{display:inline-block;padding:4px 8px;border-radius:999px;background:#eef4ff;color:#3538cd;font-weight:bold;font-size:12px}
.btn{background:#1570ef;color:#fff;border:none;padding:11px 16px;border-radius:10px;font-weight:bold;cursor:pointer}
.btn:disabled{opacity:.6;cursor:not-allowed}
#progress{display:none;margin-top:14px}.spinner{width:28px;height:28px;border:4px solid #d1e0ff;border-top-color:#1570ef;border-radius:50%;animation:spin .8s linear infinite;display:inline-block;vertical-align:middle}
@keyframes spin{to{transform:rotate(360deg)}}
#log{margin-top:12px;background:#101828;color:#d0d5dd;padding:12px;border-radius:8px;max-height:260px;overflow:auto;font-family:monospace;font-size:12px}
.notice{padding:8px 10px;border-radius:8px;background:#fffaeb;border:1px solid #fedf89;margin-bottom:10px}
</style>
</head>
<body>
<div class="wrap">
  <div class="card">
    <h1>ticketsCAD Installer <span class="badge"><?php echo h($installerVersion); ?></span></h1>
    <p class="muted">Installed version: <strong><?php echo h($detection['installed_version'] === null ? 'not detected' : $detection['installed_version']); ?></strong></p>
    <?php if ($detection['legacy']) { ?><div class="notice">Detected settings table without a _version value. This appears to be an unknown legacy install.</div><?php } ?>
    <p class="muted">System details: PHP <?php echo h(PHP_VERSION); ?> · OS <?php echo h(PHP_OS); ?> · Web <?php echo h($serverSoftware); ?> · DB <?php echo h($detection['db_version'] ? $detection['db_version'] : 'not connected'); ?></p>

    <form id="installerForm">
      <div class="grid">
        <div><label>Mode</label>
          <div class="mode-switch" id="modeSwitch">
            <label class="mode-option <?php echo $modeDefault === 'install_clean' ? 'active' : ''; ?>">
              <input type="radio" name="mode" value="install_clean" <?php echo $modeDefault === 'install_clean' ? 'checked' : ''; ?>>
              <span class="mode-title">Install / Reinstall</span>
              <span class="mode-desc">Drop all tables and rebuild clean.</span>
            </label>
            <label class="mode-option <?php echo $modeDefault === 'upgrade' ? 'active' : ''; ?> <?php echo $detection['exists'] ? '' : 'disabled'; ?>">
              <input type="radio" name="mode" value="upgrade" <?php echo $modeDefault === 'upgrade' ? 'checked' : ''; ?> <?php echo $detection['exists'] ? '' : 'disabled'; ?>>
              <span class="mode-title">Upgrade</span>
              <span class="mode-desc">Create missing tables and sync columns.</span>
            </label>
            <label class="mode-option">
              <input type="radio" name="mode" value="write_config">
              <span class="mode-title">Write Config</span>
              <span class="mode-desc">Save DB credentials only.</span>
            </label>
          </div>
        </div>
        <div><label>Table prefix (optional)</label><input name="db_prefix" value="<?php echo h($defaults['prefix']); ?>"></div>
        <div><label>MySQL host</label><input name="db_host" required value="<?php echo h($defaults['host']); ?>"></div>
        <div><label>MySQL database</label><input name="db_name" required value="<?php echo h($defaults['db']); ?>"></div>
        <div><label>MySQL username</label><input name="db_user" required value="<?php echo h($defaults['user']); ?>"></div>
        <div><label>MySQL password</label><input type="password" name="db_pass" value="<?php echo h($defaults['pass']); ?>"></div>

        <div><label>First admin username</label><input id="admin_user" name="admin_user" value="admin"></div>
        <div><label>First admin display name</label><input id="admin_name" name="admin_name" value="Administrator"></div>
        <div><label>First admin password</label><input type="password" id="admin_pass" name="admin_pass"></div>
        <div><label>Confirm password</label><input type="password" id="admin_pass_confirm"></div>
      </div>
      <p id="passStatus" class="muted"></p>
      <button class="btn" id="runBtn" type="submit">Do It</button>
      <button class="btn" id="resetBtn" type="reset" style="background:#475467;margin-left:8px;">Reset Form</button>
    </form>

    <div id="progress"><span class="spinner"></span> <strong>Working...</strong></div>
    <div id="log"></div>
  </div>
</div>
<script>
(function(){
  var f=document.getElementById('installerForm'),log=document.getElementById('log'),prog=document.getElementById('progress'),btn=document.getElementById('runBtn');
  var pass=document.getElementById('admin_pass'),confirmPass=document.getElementById('admin_pass_confirm'),status=document.getElementById('passStatus');
  var modeInputs=[].slice.call(document.querySelectorAll('input[name="mode"]'));
  function getMode(){ var c=modeInputs.find(function(i){return i.checked;}); return c?c.value:'install_clean'; }
  function updateModeCards(){ modeInputs.forEach(function(i){ var c=i.closest('.mode-option'); if(!c){return;} c.classList.toggle('active', i.checked); }); }

  function validatePass(){
    if(getMode()==='write_config'){status.textContent='';return true;}
    if(pass.value.length<6){status.textContent='Password must be at least 6 characters.';status.style.color='#b42318';return false;}
    if(pass.value!==confirmPass.value){status.textContent='Passwords do not match.';status.style.color='#b42318';return false;}
    status.textContent='Passwords match.';status.style.color='#027a48';return true;
  }

  pass.addEventListener('input',validatePass);
  confirmPass.addEventListener('input',validatePass);
  modeInputs.forEach(function(i){ i.addEventListener('change', function(){ updateModeCards(); validatePass(); }); });
  updateModeCards();

  f.addEventListener('submit', function(e){
    e.preventDefault();
    if(!validatePass()){return;}

    var data = new FormData(f);
    data.append('action','execute_stream');
    prog.style.display='block';
    btn.disabled=true;
    document.getElementById('resetBtn').disabled=true;
    log.textContent='';

    var xhr = new XMLHttpRequest();
    var seen = 0;
    xhr.open('POST', 'install.php', true);
    xhr.onprogress = function(){
      var chunk = xhr.responseText.substring(seen);
      seen = xhr.responseText.length;
      if(chunk){ log.textContent += chunk; log.scrollTop = log.scrollHeight; }
    };
    xhr.onload = function(){
      prog.style.display='none';
      var text = xhr.responseText || '';
      if(text.indexOf('DONE:1') !== -1){
        log.textContent += '\nInstall complete. Open: index.php\n';
        var doneLink = document.createElement('a');
        doneLink.href='index.php'; doneLink.textContent='Go to TicketsCAD';
        doneLink.style.display='inline-block'; doneLink.style.marginTop='10px'; doneLink.style.fontWeight='bold';
        log.parentNode.appendChild(doneLink);
        btn.textContent='Done';
        btn.disabled=true;
      } else {
        log.textContent += '\nCompleted with errors.\n';
        btn.disabled=false;
        document.getElementById('resetBtn').disabled=false;
      }
    };
    xhr.onerror = function(){
      prog.style.display='none';
      log.textContent += '\nInstaller request failed.';
      btn.disabled=false;
      document.getElementById('resetBtn').disabled=false;
    };
    xhr.send(data);
  });
})();
</script>
</body>
</html>
