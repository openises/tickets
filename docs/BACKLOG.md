# Tickets CAD - Feature Backlog

Items below are enhancements and improvements identified during the security modernization effort (Phase 1). They are not bugs or security issues and are deferred to a future release (likely Phase 4 - UI Changes).

---

## 1. Improve Required-Field UX on Forms

**Source:** Manual testing of Add Incident form (2026-03-14)
**Reporter:** Eric Osterberg
**Affected screens:** Add/Edit Incident forms; likely applies to all data-entry forms (Units, Facilities, etc.)

### Current behavior
- Required fields are marked with red asterisks (`*`).
- When a user submits with missing required data, a server-side error message is displayed.
- The error message lists missing fields, but the form itself gives no visual feedback about which fields still need attention.

### Problem
- Red asterisks are easy to overlook, especially on long forms.
- After an error, the user has to read the error text and manually locate the empty fields.
- Submitting with minimal data produced an error that could be prevented with better client-side guidance.

### Proposed improvement
- **Highlight required fields** (e.g., light yellow or red border) until the user fills them in.
- **Client-side validation** before submit: prevent the form from posting if required fields are empty, and scroll to / focus the first empty required field.
- **Inline feedback:** Show a small message next to each empty required field on blur or on submit attempt (e.g., "This field is required").
- **Clean up asterisk styling:** Make the asterisk placement and color consistent across all forms.
- Consider a subtle **visual transition** (border color fade) when a required field goes from empty to filled.

### Implementation notes
- Most forms use a shared include (`forms/inc_wizard.php` or inline HTML). A centralized JS validation helper + CSS class (`.required-empty`, `.required-filled`) could cover many forms with minimal per-form changes.
- MooTools is the existing JS framework; any validation JS should use MooTools conventions unless the project migrates to vanilla JS / another library first.
- This touches UI/UX (Phase 4 in the modernization plan), not security (Phase 1).

### Acceptance criteria
- [ ] Required fields are visually distinct before the user interacts with them
- [ ] Submitting with empty required fields shows inline feedback without a full page reload
- [ ] Error styling clears automatically when the user fills in the field
- [ ] Works consistently across Add/Edit Incident, Units, Facilities, and other data-entry forms

---

## 2. Demo Configuration Templates & Data Management

**Source:** Discussion during testing session (2026-03-14)
**Reporter:** Eric Osterberg

### Overview
Provide a template system that lets users load, manage, and share pre-built demo configurations. On initial installation, users would see a selection of included templates to quickly populate the system with realistic demo data for their use case.

### Included starter templates
1. **Small Fire Department** - Stations, engines, ladder trucks, personnel, typical incident types (structure fire, vehicle accident, medical assist, etc.)
2. **Small Medical Operation** - Ambulances, medic units, hospitals/facilities, medical incident types (cardiac, trauma, transport, etc.)
3. **Amateur Radio / Marathon Route** - Net control stations, volunteer operators, aid stations along a route, event-specific incident types (runner down, lost person, traffic control, etc.)

### Key features
- **Template browser on install:** During initial setup, the installer presents available templates the user can choose to pre-load.
- **Load / Unload:** Users can load a template to populate the system and unload it to clear that data set — useful for switching between annual events or training scenarios.
- **Download / Upload / Share:** Templates can be exported as a file (JSON or SQL dump) and shared with other installations. Users can import templates from other organizations.
- **Cleanup function:** Clear all demo/template data from the system without affecting configuration settings. Useful for transitioning from training to production use.
- **Archive function:** Archive old event data (incidents, logs, messages) to a separate store before loading a new event template. Preserves historical data while giving a clean workspace.
- **User-created templates:** Users can save their current configuration as a new template for reuse or sharing.

### Use cases
- **New installations:** First-time users get a feel for the system with realistic sample data instead of an empty database.
- **Training:** Load a training template before a training session, run exercises, then unload/archive when done.
- **Annual events:** Volunteer organizations (e.g., amateur radio groups supporting marathons, parades, or disaster exercises) load event-specific templates each year, archive when the event ends.
- **Multi-purpose organizations:** Switch between configurations for different operational modes.

### Implementation notes
- Templates could be stored as JSON files in a `templates/` directory, each containing table data for units, facilities, incident types, status codes, etc.
- The installer (`install.php`) already has upgrade/install modes — a "Load Template" option could be added.
- A management screen (admin menu) for browsing, loading, unloading, and archiving templates.
- Load/unload should be non-destructive to system settings (`settings` table, `mysql.inc.php` config).
- Archive could use date-stamped SQL dumps or a separate archive database/tables.
- Consider a manifest file in each template with metadata: name, description, author, version, use-case tags, table list.

### Acceptance criteria
- [ ] At least 3 starter templates ship with the installation package
- [ ] Installer offers template selection during initial setup
- [ ] Admin screen allows loading, unloading, downloading, uploading, and deleting templates
- [ ] Cleanup function removes all operational data (incidents, units, facilities, logs) while preserving system config
- [ ] Archive function snapshots current data before cleanup
- [ ] Templates can be exported as portable files and imported on another installation
- [ ] Loading a template does not overwrite system settings or user accounts

---

## 3. Intelligent Map Tile Caching

**Source:** Discussion during testing session (2026-03-14)
**Reporter:** Eric Osterberg

### Overview
Cache map tiles locally on the server whenever they are loaded from the upstream tile provider (e.g., OpenStreetMap). Serve cached tiles when available, falling back to the upstream source only when a tile is missing or expired. This provides resilience against internet outages — as long as an area has been viewed before, the map remains functional offline.

### Key features
- **Transparent caching:** When a map tile is requested, the server checks the local cache first. On cache miss, it fetches from upstream, stores locally, and serves to the client.
- **Configurable TTL (time-to-live):** Admin setting for how long cached tiles remain valid (e.g., 7 days, 30 days, 90 days). Tiles older than the TTL are replaced the next time the same tile is fetched from upstream.
- **Lazy refresh:** Expired tiles are still served if the upstream source is unreachable (graceful degradation). They are replaced silently when connectivity returns and the tile is next requested.
- **Enable/disable toggle:** Configurable option in admin settings — some installations may not want or need caching (e.g., cloud-hosted with reliable internet).
- **Cache size management:** Optional max cache size setting with LRU (least recently used) eviction, or a manual "clear cache" button in admin.
- **Zoom level limits:** Optionally limit which zoom levels are cached to manage disk usage (e.g., only cache zoom 10-18, skip very high or very low zoom).

### How it works
1. Client requests a tile via Leaflet (e.g., `/{z}/{x}/{y}.png`)
2. Instead of hitting the tile provider directly, the request goes through a local PHP proxy endpoint (e.g., `ajax/tile_proxy.php?z=14&x=8192&y=5461`)
3. The proxy checks `cache/tiles/{z}/{x}/{y}.png` on disk
4. If the cached file exists and is within TTL → serve it directly with appropriate cache headers
5. If the cached file is missing or expired → fetch from upstream, store to disk, serve to client
6. If upstream is unreachable and a stale cached file exists → serve the stale tile (better than nothing)
7. If upstream is unreachable and no cached file exists → return a placeholder "tile unavailable" image

### Implementation notes
- Tile cache directory: `cache/tiles/{z}/{x}/{y}.png` — standard slippy map directory structure
- PHP proxy endpoint handles the fetch/cache/serve logic; Leaflet's tile URL template is pointed at it instead of directly at the tile provider
- Respect tile provider terms of service (OSM allows caching with proper attribution and `User-Agent`)
- Store tile metadata (fetch timestamp) either as file modification time or in a lightweight SQLite/JSON index
- Consider supporting multiple tile providers (OSM, satellite, topo) with separate cache namespaces
- The proxy should set `Content-Type: image/png` and appropriate browser cache headers
- For Raspberry Pi deployments with limited SD card space, zoom level limits and max cache size are important
- A pre-cache / seed feature (future enhancement) could allow admins to pre-download tiles for a bounding box + zoom range before deploying to a field location

### Use cases
- **Field deployments:** Amateur radio operators at a marathon or disaster exercise may have spotty or no internet. Pre-viewed areas remain available on the map.
- **Remote stations:** Fire departments or medical ops in rural areas with unreliable connectivity.
- **Bandwidth conservation:** Reduce repeated tile downloads for the same area across multiple users/sessions.
- **Training environments:** Load the map once during setup, then run training exercises without depending on internet.

### Admin settings
- `tile_cache_enabled` — ON/OFF (default: OFF)
- `tile_cache_ttl` — Days before a cached tile is considered stale (default: 30)
- `tile_cache_max_size_mb` — Maximum disk usage for tile cache in MB (default: 500, 0 = unlimited)
- `tile_cache_zoom_min` — Minimum zoom level to cache (default: 1)
- `tile_cache_zoom_max` — Maximum zoom level to cache (default: 19)

### Acceptance criteria
- [ ] Tile proxy endpoint serves tiles from cache when available
- [ ] Cache miss triggers upstream fetch, stores tile, and serves it transparently
- [ ] Cached tiles are refreshed when older than the configured TTL
- [ ] Stale cached tiles are served when upstream is unreachable (offline resilience)
- [ ] Admin can enable/disable caching, set TTL, and set max cache size
- [ ] Cache respects disk space limits and evicts oldest/least-used tiles when full
- [ ] Map works normally from the user's perspective — caching is invisible
- [ ] Tile provider terms of service are respected (User-Agent, attribution)

---

## 4. Local Map Missing Tile System Crash (Critical Stability Issue)

**Source:** Discussion during testing session (2026-03-14)
**Reporter:** Eric Osterberg
**Category:** Bug / System Stability
**Priority:** Critical

### Overview
When local maps are configured (`local_maps = 1`) and there are missing tiles,
errors are logged continuously until the system crashes — either because all
resources are consumed or the disk fills up. The load average stays maxed out
even after the browser is closed and Apache is stopped, requiring a system
reboot to recover.

### Partial fix applied (3/14/26)
**Browser-side 404 flooding:** Added Leaflet `errorTileUrl` option with a
transparent 1x1 PNG data URI in `osm_map_functions.js` and
`config.setcenter.inc.php`. This prevents Leaflet from continuously retrying
failed tile requests, eliminating the 404 log flood from the browser side.

This does NOT address the full issue — the crash persists even after closing
the browser and stopping Apache, indicating a deeper server-side or OS-level
problem.

### Root cause analysis (needs live investigation with local maps configured)

1. **`get_tile_bounds()` filesystem traversal on every page load:**
   - Located in `functions.inc.php` (line ~4644)
   - Called from ~40 PHP files via `all_forms_js_variables.inc.php` and inline
   - Does `opendir()`/`readdir()` traversal of tile directories
   - At high zoom levels (15+), tile directories can contain hundreds of
     thousands of subdirectories and files
   - Every page load = full directory scan = expensive disk I/O
   - The situation screen auto-refreshes every 10-30 seconds, compounding this

2. **Orphaned PHP processes with `set_time_limit(0)`:**
   - 20+ scripts set `set_time_limit(0)`, including the main situation screen
     (`full_scr.php`, `full_sit_scr.php`, `cb.php`)
   - If a PHP process gets stuck doing filesystem operations on a massive tile
     directory, it runs indefinitely — even after Apache's parent process stops
   - Multiple orphaned PHP processes doing filesystem I/O simultaneously could
     explain why load stays high after Apache shutdown

3. **Cascading disk I/O and error log growth:**
   - PHP warnings from `opendir()` on missing/corrupt paths write to error log
   - Apache 404 errors from missing tile requests write to error log
   - Rapid error log growth saturates disk I/O
   - Disk I/O saturation causes other processes (including the OS) to queue
   - Even after Apache stops, filesystem journal recovery from a multi-GB log
     file can keep the system loaded

4. **No error handling in `get_tile_bounds()`:**
   - `opendir()` returns FALSE on failure but the return value is used directly
     in `readdir()` without checking — generates continuous PHP warnings
   - `low_high_dir()` helper has the same issue
   - No guards against missing or incomplete tile directory structures

### Recommended fixes

**Short-term (next release):**
- Add error checking to `get_tile_bounds()` — guard `opendir()` returns
- Cache the bounds result in a database setting or flat file — only recalculate
  when tiles are added or removed (via `gettiles.php` / `deltile.php`)
- Remove `set_time_limit(0)` from page-rendering scripts (keep it only on
  genuinely long-running operations like tile download and database import)

**Medium-term:**
- Add Apache error log rotation configuration to installation docs
- Implement a tile coverage health check that warns admins when coverage is
  incomplete for the configured area
- Add a Leaflet-side `tileerror` counter that shows a user warning when too
  many tiles are missing (e.g., "X tiles could not be loaded — check your
  local map configuration")

**Long-term:**
- Replace `get_tile_bounds()` filesystem traversal with metadata stored during
  tile download — the download process already knows the bounds
- Consider the Intelligent Map Tile Caching feature (Backlog #3) as a
  comprehensive replacement for the current local maps system

### Files involved
- `incs/functions.inc.php` — `get_tile_bounds()` function (line ~4644)
- `incs/all_forms_js_variables.inc.php` — calls `get_tile_bounds()` (line 237)
- `incs/variables.inc.php` — tile directory scanning (lines 40-51)
- `js/osm_map_functions.js` — tile layer creation, errorTileUrl fix
- `incs/config.setcenter.inc.php` — tile layer creation, errorTileUrl fix
- `ajax/gettiles.php` — tile download process
- `get_tiles.php` — tile management UI
- ~40 form/screen files that call `get_tile_bounds()` inline

### Acceptance criteria
- [ ] System remains stable with local maps configured and missing tiles
- [ ] No orphaned PHP processes after closing browser / stopping Apache
- [ ] Error log does not grow unbounded from tile-related errors
- [ ] `get_tile_bounds()` result is cached and not recalculated on every page load
- [ ] Missing tiles produce a clear admin warning, not a system crash

---

## 5. Full Screen View Dropdown Not Fully Working

**Source:** Manual testing (2026-03-16)
**Reporter:** Eric Osterberg
**Category:** Bug / UI
**Affected screen:** Full screen view (`full_scr.php`)

### Current behavior
The dropdown list of views (e.g., "Current situation", "Incidents closed today", "Incidents closed last month", etc.) does not fully work. Selecting certain options from the dropdown does not update the display as expected.

### Expected behavior
All dropdown options should filter/update the displayed incidents according to the selected time range or view.

### Investigation needed
- [ ] Identify which dropdown options work and which do not
- [ ] Check the JavaScript handler for the dropdown change event
- [ ] Verify the AJAX/form submission that loads the filtered data
- [ ] Test each option and document which ones fail

---

## 6. Links Button Inconsistent Across Pages

**Source:** Manual testing (2026-03-16)
**Reporter:** Eric Osterberg
**Category:** Bug / UI

### Current behavior
The "links" button works on some pages (Situation, New, Units, Fac's) but does not load on other pages.

### Expected behavior
The links button should work consistently across all pages/modules.

### Investigation needed
- [ ] Identify which pages include the links button and which do not
- [ ] Compare the working pages (Situation, New, Units, Fac's) with the non-working ones
- [ ] Check if the links functionality is loaded via an include file that some pages are missing
- [ ] Determine if it's a missing include, a JavaScript loading issue, or a conditional rendering problem

---

*Add new backlog items below using the same format.*
