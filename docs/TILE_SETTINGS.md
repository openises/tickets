# Tile Settings Guide

Tickets CAD displays maps using [Leaflet](https://leafletjs.com/) with raster map tiles.
This guide explains how to configure where tiles come from, how caching works,
and how to set up your own tile server for fully offline deployments.

## Why This Matters

OpenStreetMap's [tile usage policy](https://operations.osmfoundation.org/policies/tiles/)
requires applications to:

- Use a valid HTTP User-Agent identifying the application
- Cache tiles locally and honor HTTP 304 responses
- Not pre-fetch (bulk download) tiles that users are not actively viewing

The **Proxy Cache** mode was built to comply with these requirements. It caches tiles
on demand as users view the map, sends a proper `TicketsCAD/vX.X.X` User-Agent,
and uses conditional `If-Modified-Since` requests for expired tiles.

## Tile Source Modes

Configure tile mode from **Config > Tile Settings** in the admin panel.

### Online Direct

Each user's browser fetches tiles directly from the tile server. No local caching.
This is the simplest setup but generates the most external traffic.

**Use when:** You have reliable internet and low user counts.

### Proxy Cache (Recommended)

The Tickets CAD server fetches tiles on behalf of users and caches them locally in
`_osm/tiles/{z}/{x}/{y}.png`. Subsequent requests for the same tile are served
from cache until the cache expires.

**Use when:** You want faster map loading, reduced bandwidth, and OSM policy compliance.

### Offline Local

Tiles are served from pre-loaded files in `_osm/tiles/`. No internet access needed.
Tiles must be loaded using the **Download Maps** page with your own tile server.
Bulk downloading from OSM is prohibited by their tile usage policy.

**Use when:** You need fully air-gapped maps with no internet dependency.

## Settings Reference

| Setting | Default | Description |
|---------|---------|-------------|
| **Tile Source Mode** | Online Direct | How tiles are obtained (online/proxy/offline) |
| **Tile Server URL** | `https://tile.openstreetmap.org/{z}/{x}/{y}.png` | URL template for tile requests. Use `{z}`, `{x}`, `{y}` for coordinates and `{s}` for subdomain rotation |
| **Cache Duration** | 60 days | How long cached tiles are kept before re-fetching (proxy mode only). Set to 0 to bypass cache |

### Tile Server URL Placeholders

- `{z}` — Zoom level (0–20)
- `{x}` — X tile coordinate
- `{y}` — Y tile coordinate
- `{s}` — Subdomain, randomly chosen from a, b, c (for load balancing)

## Recommended Settings

### Most Installations

- **Mode:** Proxy Cache
- **URL:** Default OSM URL (leave as-is)
- **Cache Duration:** 60 days

### Custom Tile Server

- **Mode:** Proxy Cache or Online Direct
- **URL:** Your server's URL template, e.g. `https://tiles.example.com/{z}/{x}/{y}.png`
- **Cache Duration:** Adjust based on how often your tiles update

### Air-Gapped / Offline

- **Mode:** Offline Local
- Pre-load tiles using the Download Maps page pointed at your own tile server

### Debugging / Forced Refresh

If map tiles appear outdated:

1. Set **Cache Duration** to **0** (forces fresh fetch on every request)
2. Reload the map in your browser (Ctrl+Shift+R for hard refresh)
3. Restore cache duration to its previous value

## Cache Behavior

In proxy mode, the caching proxy (`tile_proxy.php`) works as follows:

1. User's browser requests a tile via `tile_proxy.php?z=Z&x=X&y=Y`
2. If a cached copy exists and is younger than the cache duration, serve it immediately
3. If no cache or cache is expired, fetch from the upstream tile server:
   - If a stale cached copy exists, send an `If-Modified-Since` header
   - On HTTP 304 (Not Modified), refresh the cache timestamp and serve the stale copy
   - On HTTP 200, save the new tile to cache and serve it
   - On error, serve the stale copy if available, otherwise return a transparent 1px PNG
4. Tiles are stored in `_osm/tiles/{z}/{x}/{y}.png` (same directory used by the bulk downloader)

The proxy sends a `TicketsCAD/vX.X.X` User-Agent with every outbound request.

## Self-Hosted Tile Servers

If you need fully offline maps without depending on external tile servers, you can
run your own tile server. Here are the most common approaches:

### Switch2OSM (Traditional Stack)

Step-by-step guides for setting up a full OpenStreetMap tile server using
renderd + mod_tile + Apache + PostgreSQL/PostGIS.

- **Difficulty:** Advanced
- **Website:** https://switch2osm.org/

### openstreetmap-tile-server (Docker)

A pre-built Docker image that runs a complete OSM tile server. You provide a
`.osm.pbf` data file and it handles everything else.

- **Difficulty:** Medium
- **GitHub:** https://github.com/Overv/openstreetmap-tile-server

### OpenMapTiles

Generate your own vector or raster tiles from OSM data with custom styling.
Pre-built regional tile extracts are available for download.

- **Difficulty:** Medium
- **Website:** https://openmaptiles.org/

### TileServer GL

Lightweight tile server for MBTiles files. Can serve both vector tiles and
server-side-rendered raster tiles. Works well with OpenMapTiles data.

- **Difficulty:** Easy–Medium
- **GitHub:** https://github.com/maptiler/tileserver-gl

### Tilemaker

Generates MBTiles directly from `.osm.pbf` data files with no database required.
A single executable — the simplest way to create your own tile set.

- **Difficulty:** Easy–Medium
- **GitHub:** https://github.com/systemed/tilemaker

### Where to Get Map Data

Regional OSM data extracts (`.osm.pbf` files) can be downloaded from
[Geofabrik](https://download.geofabrik.de/). These are updated daily and cover
individual countries, states, or regions.

## Files

| File | Purpose |
|------|---------|
| `tile_proxy.php` | Caching proxy endpoint (serves tiles in proxy mode) |
| `incs/config.tiles.inc.php` | Tile Settings configuration panel |
| `incs/functions.inc.php` | `get_tile_mode()`, `get_tile_url()`, `get_tile_user_agent()` helpers |
| `js/osm_map_functions.js` | Leaflet tile layer initialization |
| `incs/all_forms_js_variables.inc.php` | Emits `tileMode` and `tileUrl` JS variables |
| `ajax/gettiles.php` | Bulk tile downloader (for offline mode with custom tile servers) |
| `get_tiles.php` | Download Maps page UI |
| `_osm/tiles/` | Tile cache directory (created automatically) |

## Version History

- **v3.44.0** — Added tile caching proxy, configurable tile server URL,
  tile source modes (online/proxy/offline), configurable cache duration,
  proper User-Agent header, and Tile Settings admin page.
