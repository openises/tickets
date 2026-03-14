<?php
/**
 * Tile Settings Configuration Panel
 * Allows admins to choose tile mode (online/proxy/offline) and configure the tile server URL.
 * 3/14/2026 - New file
 */
if (!defined('E_DEPRECATED')) { define('E_DEPRECATED', 8192); }
error_reporting(E_ALL ^ E_DEPRECATED);

// Handle form save
if (array_key_exists('update', $_GET)) {
    $new_mode = isset($_POST['frm_tile_mode']) ? sanitize_string($_POST['frm_tile_mode']) : 'online';
    if (!in_array($new_mode, array('online', 'proxy', 'offline'))) {
        $new_mode = 'online';
    }
    $new_url = isset($_POST['frm_tile_server_url']) ? trim(sanitize_string($_POST['frm_tile_server_url'])) : '';
    if ($new_url === '') {
        $new_url = 'https://tile.openstreetmap.org/{z}/{x}/{y}.png';
    }

    // Check if tile_mode setting exists; if not, INSERT it (first upgrade)
    $existing = get_variable('tile_mode');
    if ($existing === FALSE) {
        $query = "INSERT INTO `{$GLOBALS['mysql_prefix']}settings` (`name`, `value`) VALUES ('tile_mode', ?)";
        db_query($query, [$new_mode]);
    } else {
        $query = "UPDATE `{$GLOBALS['mysql_prefix']}settings` SET `value`=? WHERE `name`='tile_mode'";
        db_query($query, [$new_mode]);
    }

    $existing_url = get_variable('tile_server_url');
    if ($existing_url === FALSE) {
        $query = "INSERT INTO `{$GLOBALS['mysql_prefix']}settings` (`name`, `value`) VALUES ('tile_server_url', ?)";
        db_query($query, [$new_url]);
    } else {
        $query = "UPDATE `{$GLOBALS['mysql_prefix']}settings` SET `value`=? WHERE `name`='tile_server_url'";
        db_query($query, [$new_url]);
    }

    $new_cache_days = isset($_POST['frm_tile_cache_days']) ? intval($_POST['frm_tile_cache_days']) : 60;
    if ($new_cache_days < 0) { $new_cache_days = 0; }
    $existing_cache = get_variable('tile_cache_days');
    if ($existing_cache === FALSE) {
        $query = "INSERT INTO `{$GLOBALS['mysql_prefix']}settings` (`name`, `value`) VALUES ('tile_cache_days', ?)";
        db_query($query, [strval($new_cache_days)]);
    } else {
        $query = "UPDATE `{$GLOBALS['mysql_prefix']}settings` SET `value`=? WHERE `name`='tile_cache_days'";
        db_query($query, [strval($new_cache_days)]);
    }

    // Keep local_maps in sync for backward compatibility with 60+ files that read it
    $local_maps_val = ($new_mode === 'offline') ? '1' : '0';
    $query = "UPDATE `{$GLOBALS['mysql_prefix']}settings` SET `value`=? WHERE `name`='local_maps'";
    db_query($query, [$local_maps_val]);

    // Clear cached variables so get_variable() re-reads from DB
    $GLOBALS['variables'] = array();

    $top_notice = "Tile settings saved.";
}

// Read current values
$current_mode = get_tile_mode();
$current_url = get_variable('tile_server_url');
if ($current_url === FALSE || trim($current_url) === '') {
    $current_url = 'https://tile.openstreetmap.org/{z}/{x}/{y}.png';
}
$is_osm_url = (stripos($current_url, 'openstreetmap.org') !== false);
$current_cache_days = get_variable('tile_cache_days');
if ($current_cache_days === FALSE || trim($current_cache_days) === '') {
    $current_cache_days = '60';
}
?>

    </HEAD>
    <BODY onLoad="ck_frames();">
<?php
if (isset($top_notice)) {
    print "<DIV style='background: #D4EDDA; border: 1px solid #28A745; padding: 8px 12px; margin: 5px 10px; font-size: 13px; color: #155724;'>";
    print "<strong>" . htmlspecialchars($top_notice) . "</strong></DIV>";
}
?>

    <DIV id='banner' class='bannertext'>Tile Settings</DIV>

    <DIV style="padding: 15px 20px; max-width: 700px;">

    <FORM name="tile_settings_form" method="POST" action="config.php?func=tiles&update=true">

    <TABLE cellpadding="6" cellspacing="0" border="0" style="width: 100%;">

    <!-- Tile Mode -->
    <TR>
        <TD colspan="2" style="padding-bottom: 4px;">
            <strong style="font-size: 14px;">Tile Source Mode</strong>
        </TD>
    </TR>
    <TR>
        <TD style="padding-left: 20px; vertical-align: top; width: 30px;">
            <input type="radio" name="frm_tile_mode" id="mode_online" value="online"
                <?php if ($current_mode == 'online') echo 'checked'; ?>
                onchange="updateModeInfo();" />
        </TD>
        <TD>
            <label for="mode_online"><strong>Online Direct</strong></label><br />
            <span style="font-size: 11px; color: #666;">
                Tiles are loaded directly from the tile server by each user's browser.
                No local caching. Requires internet access.
            </span>
        </TD>
    </TR>
    <TR>
        <TD style="padding-left: 20px; vertical-align: top;">
            <input type="radio" name="frm_tile_mode" id="mode_proxy" value="proxy"
                <?php if ($current_mode == 'proxy') echo 'checked'; ?>
                onchange="updateModeInfo();" />
        </TD>
        <TD>
            <label for="mode_proxy"><strong>Proxy Cache</strong> (Recommended)</label><br />
            <span style="font-size: 11px; color: #666;">
                Tiles are fetched through the Tickets CAD server and cached locally.
                Reduces external requests and complies with OSM tile usage policy.
                Tiles are cached on demand as users view the map. Cache duration is
                controlled by the <strong>Cache Duration</strong> setting below.
            </span>
        </TD>
    </TR>
    <TR>
        <TD style="padding-left: 20px; vertical-align: top;">
            <input type="radio" name="frm_tile_mode" id="mode_offline" value="offline"
                <?php if ($current_mode == 'offline') echo 'checked'; ?>
                onchange="updateModeInfo();" />
        </TD>
        <TD>
            <label for="mode_offline"><strong>Offline Local</strong></label><br />
            <span style="font-size: 11px; color: #666;">
                Tiles are served from locally stored files only. No internet access needed.
                Use the <a href="get_tiles.php">Download Maps</a> page to pre-load tiles
                (requires your own tile server &mdash; bulk downloading from OSM is prohibited).
            </span>
        </TD>
    </TR>

    <!-- Tile Server URL -->
    <TR>
        <TD colspan="2" style="padding-top: 15px; padding-bottom: 4px;">
            <strong style="font-size: 14px;">Tile Server URL</strong>
        </TD>
    </TR>
    <TR>
        <TD colspan="2" style="padding-left: 20px;">
            <input type="text" name="frm_tile_server_url" id="tile_server_url"
                value="<?php echo htmlspecialchars($current_url); ?>"
                style="width: 100%; font-size: 13px; padding: 4px 6px; font-family: monospace;" />
            <br />
            <span style="font-size: 11px; color: #666;">
                URL template for tile requests. Use <code>{z}</code>, <code>{x}</code>, <code>{y}</code> for tile coordinates
                and <code>{s}</code> for subdomain (a/b/c).<br />
                Default: <code>https://tile.openstreetmap.org/{z}/{x}/{y}.png</code>
            </span>
        </TD>
    </TR>

    <!-- OSM Policy Warning -->
    <TR id="osm_warning" style="<?php echo $is_osm_url ? '' : 'display:none;'; ?>">
        <TD colspan="2" style="padding-top: 10px;">
            <DIV style="background: #FFF3CD; border: 1px solid #FFC107; padding: 8px 12px; font-size: 12px; color: #856404;">
                <strong>Note:</strong> OpenStreetMap's
                <a href="https://operations.osmfoundation.org/policies/tiles/" target="_blank" style="color: #533f03;">tile usage policy</a>
                prohibits bulk downloading from their servers. When using OSM tiles,
                <strong>Proxy Cache</strong> mode is recommended. The bulk download tool should only be used
                with your own tile server.
            </DIV>
        </TD>
    </TR>

    <!-- Cache Duration (Proxy mode) -->
    <TR>
        <TD colspan="2" style="padding-top: 15px; padding-bottom: 4px;">
            <strong style="font-size: 14px;">Cache Duration (days)</strong>
        </TD>
    </TR>
    <TR>
        <TD colspan="2" style="padding-left: 20px;">
            <input type="number" name="frm_tile_cache_days" id="tile_cache_days"
                value="<?php echo intval($current_cache_days); ?>"
                min="0" max="365"
                style="width: 80px; font-size: 13px; padding: 4px 6px;" />
            <br />
            <span style="font-size: 11px; color: #666;">
                Number of days to keep cached tiles before re-fetching from the tile server.
                Applies to <strong>Proxy Cache</strong> mode only. Default: <strong>60</strong> days.<br />
                Set to <strong>0</strong> to bypass the cache and always fetch fresh tiles (useful for
                debugging or after a tile server update). Map tiles change infrequently, so
                higher values reduce load on the tile server.
            </span>
        </TD>
    </TR>

    <!-- User-Agent Info -->
    <TR>
        <TD colspan="2" style="padding-top: 15px;">
            <strong style="font-size: 14px;">User-Agent</strong>
        </TD>
    </TR>
    <TR>
        <TD colspan="2" style="padding-left: 20px;">
            <span style="font-size: 12px; font-family: monospace; background: #F0F0F0; padding: 2px 6px;">
                <?php echo htmlspecialchars(get_tile_user_agent()); ?>
            </span>
            <br />
            <span style="font-size: 11px; color: #666;">
                Sent with all outbound tile requests (proxy and bulk download). Updates automatically with the application version.
            </span>
        </TD>
    </TR>

    <!-- Save Button -->
    <TR>
        <TD colspan="2" style="padding-top: 20px; text-align: center;">
            <input type="submit" value="Save Tile Settings"
                style="padding: 6px 20px; font-size: 13px; font-weight: bold; cursor: pointer;" />
            &nbsp;&nbsp;
            <input type="button" value="Back to Config" onclick="window.location='config.php';"
                style="padding: 6px 20px; font-size: 13px; cursor: pointer;" />
        </TD>
    </TR>

    </TABLE>
    </FORM>
    </DIV>

    <!-- Documentation -->
    <DIV style="margin-top: 20px; padding: 15px; background: #F8F9FA; border: 1px solid #DEE2E6; font-size: 12px; line-height: 1.6;">
        <strong style="font-size: 14px;">About Tile Modes</strong>
        <p style="margin: 8px 0 4px 0;">
            Tickets CAD displays map tiles using <a href="https://leafletjs.com/" target="_blank">Leaflet</a>.
            Tiles are small 256&times;256 pixel images that combine to form the map. The tile source mode
            controls how these images are obtained:
        </p>
        <ul style="margin: 4px 0 8px 15px; padding: 0;">
            <li><strong>Online Direct</strong> &mdash; Each user's browser fetches tiles directly from the
                tile server. Simple setup, but every user makes external requests and there is no local cache.</li>
            <li><strong>Proxy Cache</strong> (recommended) &mdash; The Tickets CAD server fetches tiles on behalf of users
                and caches them locally. Subsequent requests for the same tile are served from the cache.
                This reduces external bandwidth, speeds up map loading for all users, and complies with
                <a href="https://operations.osmfoundation.org/policies/tiles/" target="_blank">OSM's tile usage policy</a>.</li>
            <li><strong>Offline Local</strong> &mdash; Tiles are served exclusively from files stored on the server.
                No internet connection is needed. Tiles must be pre-loaded using the
                <a href="get_tiles.php">Download Maps</a> page with your own tile server (bulk downloading
                from OSM is prohibited by their usage policy).</li>
        </ul>

        <strong style="font-size: 14px;">Recommended Settings</strong>
        <ul style="margin: 4px 0 8px 15px; padding: 0;">
            <li>For most installations: <strong>Proxy Cache</strong> mode with the default OSM tile server URL and 60-day cache.</li>
            <li>If you have your own tile server: Set the <strong>Tile Server URL</strong> to your server's URL template and use
                either <strong>Proxy Cache</strong> or <strong>Online Direct</strong> mode.</li>
            <li>For air-gapped or offline deployments: Use <strong>Offline Local</strong> mode with tiles pre-loaded
                from your own tile server via the <a href="get_tiles.php">Download Maps</a> page.</li>
            <li>If tiles appear outdated, temporarily set <strong>Cache Duration</strong> to <strong>0</strong>,
                reload the map, then restore the previous value.</li>
        </ul>

        <strong style="font-size: 14px;">Self-Hosted Tile Servers</strong>
        <p style="margin: 8px 0 4px 0;">
            If you need fully offline maps or want to avoid depending on external tile servers,
            you can run your own tile server. Popular options include:
        </p>
        <ul style="margin: 4px 0 8px 15px; padding: 0;">
            <li><a href="https://switch2osm.org/" target="_blank"><strong>Switch2OSM</strong></a> &mdash;
                Step-by-step guides for setting up your own OpenStreetMap tile server using
                renderd + mod_tile + Apache. The most well-documented approach.</li>
            <li><a href="https://github.com/Overv/openstreetmap-tile-server" target="_blank"><strong>openstreetmap-tile-server (Docker)</strong></a> &mdash;
                Pre-built Docker image that runs a complete OSM tile server. Easiest way to get started.</li>
            <li><a href="https://openmaptiles.org/" target="_blank"><strong>OpenMapTiles</strong></a> &mdash;
                Generate your own vector or raster tiles from OSM data. Supports custom styling.</li>
            <li><a href="https://github.com/maptiler/tileserver-gl" target="_blank"><strong>TileServer GL</strong></a> &mdash;
                Lightweight server for MBTiles files. Good for serving pre-rendered tile sets.</li>
            <li><a href="https://github.com/systemed/tilemaker" target="_blank"><strong>Tilemaker</strong></a> &mdash;
                Generates tiles directly from .osm.pbf data files with no database required. A single executable.</li>
        </ul>
        <p style="margin: 8px 0 4px 0;">
            Regional OSM data extracts (.osm.pbf) can be downloaded from
            <a href="https://download.geofabrik.de/" target="_blank">Geofabrik</a>.
        </p>
        <p style="margin: 4px 0 0 0;">
            After setting up your tile server, update the <strong>Tile Server URL</strong> above to point to it.
            Use <code>{z}</code>, <code>{x}</code>, <code>{y}</code> for tile coordinates and <code>{s}</code>
            for load-balanced subdomains if supported.
        </p>
    </DIV>

<script type="text/javascript">
function updateModeInfo() {
    // Show/hide OSM warning based on URL content
    var url = document.getElementById('tile_server_url').value;
    var warning = document.getElementById('osm_warning');
    if (url.toLowerCase().indexOf('openstreetmap.org') !== -1) {
        warning.style.display = '';
    } else {
        warning.style.display = 'none';
    }
}

// Also update warning when URL changes
document.getElementById('tile_server_url').addEventListener('input', updateModeInfo);
</script>
