# Location Services Research — TicketsCAD NewUI

**Date:** 2026-04-03
**Purpose:** Integration research for APRS and OwnTracks location tracking, plus legacy provider audit.

---

## Table of Contents

1. [APRS Integration Plan](#1-aprs-integration-plan)
2. [OwnTracks Integration Plan](#2-owntracks-integration-plan)
3. [Legacy Provider Summary](#3-legacy-provider-summary)
4. [Recommended Architecture](#4-recommended-architecture)
5. [Docker Compose Example](#5-docker-compose-example)
6. [Data Flow Diagram](#6-data-flow-diagram)

---

## 1. APRS Integration Plan

### 1.1 What Is APRS

APRS (Automatic Packet Reporting System) is a digital communications protocol used by amateur radio operators to broadcast real-time position reports, weather data, and messages. It is deeply embedded in the emergency communications community and is already the most-used location provider in the legacy TicketsCAD codebase.

### 1.2 Two Integration Approaches

TicketsCAD needs both **receiving positions** and **sending messages**. There are two pathways to APRS data:

| Approach | Protocol | Use Case | Real-Time? |
|----------|----------|----------|------------|
| **aprs.fi REST API** | HTTPS polling | Position lookups, batch queries | No (poll every 1+ min) |
| **APRS-IS TCP stream** | TCP socket | Live position feed, send messages | Yes (sub-second) |

### 1.3 aprs.fi REST API (Current Legacy Approach)

The legacy system uses the aprs.fi REST API exclusively. This is the simplest integration path.

**Endpoint:** `https://api.aprs.fi/api/get`

**Authentication:** Requires a free API key from an aprs.fi user account (found in account settings). The legacy system stores this in the `settings` table as `aprs_fi_key`.

**Key Parameters:**

| Parameter | Value | Notes |
|-----------|-------|-------|
| `name` | Comma-separated callsigns | Max 20 per request |
| `what` | `loc` (positions), `wx` (weather), `msg` (messages) | |
| `apikey` | Your API key | |
| `format` | `json` or `xml` | |

**Response Fields (location query):**

| Field | Type | Description |
|-------|------|-------------|
| `name` | string | Callsign with SSID |
| `lat` | float | Latitude (decimal degrees) |
| `lng` | float | Longitude (decimal degrees) |
| `time` | int | Unix timestamp of position |
| `lasttime` | int | Unix timestamp of last packet |
| `speed` | float | km/h |
| `course` | float | Degrees |
| `altitude` | float | Meters |
| `comment` | string | Station status/comment |
| `path` | string | Digipeater path |
| `symbol` | string | APRS symbol table/code |

**Rate Limits:**
- API is for querying specific stations only (no wildcard searches)
- Must be used in applications that are free and publicly available
- Recommended polling interval: 1 minute minimum (legacy uses 1 min)

**Limitations:**
- Not real-time (polling-based, ~1 min latency minimum)
- Cannot send messages
- Cannot filter by geographic area efficiently
- Rate-limited for high-frequency polling

### 1.4 APRS-IS TCP Stream (Recommended for NewUI)

For real-time position tracking, the NewUI should implement a direct APRS-IS TCP connection. This enables sub-second position updates and message sending.

**Connection Details:**

| Parameter | Value |
|-----------|-------|
| Server | `rotate.aprs2.net` (Tier 2 round-robin) |
| Port | `14580` |
| Protocol | Text lines terminated by CR+LF |
| Auth | `user CALLSIGN pass PASSCODE vers TicketsCAD 4.0 filter FILTERSTRING` |

**Regional Servers (for lower latency):**

| Region | Hostname |
|--------|----------|
| North America | `noam.aprs2.net` |
| Europe | `euro.aprs2.net` |
| Asia | `asia.aprs2.net` |
| South America | `soam.aprs2.net` |
| Oceania | `aunz.aprs2.net` |

**Authentication — APRS Passcode Algorithm:**

The APRS passcode is computed from the callsign (without SSID):

1. Start with seed value `0x73E2` (29666 decimal)
2. Process callsign characters in pairs:
   - XOR the hash with (first char ASCII * 256)
   - XOR the hash with (second char ASCII)
3. Mask result to 15 bits: `hash & 0x7FFF`
4. Result is a number 0-32767

PHP implementation:
```php
function aprs_passcode($callsign) {
    $call = strtoupper(explode('-', $callsign)[0]); // strip SSID
    $hash = 0x73E2;
    for ($i = 0; $i < strlen($call); $i += 2) {
        $hash ^= ord($call[$i]) << 8;
        if ($i + 1 < strlen($call)) {
            $hash ^= ord($call[$i + 1]);
        }
    }
    return $hash & 0x7FFF;
}
```

**Note:** The passcode system is not cryptographically secure. It exists as a gentleman's agreement to restrict APRS-IS write access to licensed amateur radio operators. A passcode of `-1` allows read-only access without authentication.

**Server-Side Filters:**

Filters control which packets the server sends to you. Key filter types:

| Filter | Syntax | Example | Description |
|--------|--------|---------|-------------|
| Range | `r/lat/lon/dist` | `r/44.97/-93.26/100` | All stations within 100 km of coords |
| Budlist | `b/CALL1/CALL2` | `b/KD0ABC/W0XYZ` | Specific callsigns only |
| Prefix | `p/PREFIX` | `p/KD0/W0` | Callsign prefixes |
| Object | `o/OBJ` | `o/FIRE*` | Named objects |
| Type | `t/TYPE` | `t/poimqstunw` | Packet types (p=position, m=message, w=weather) |
| Friend range | `f/CALL/dist` | `f/KD0ABC/50` | Within dist km of a friend station |
| Exclude | `-p/PREFIX` | `-p/RELAY` | Exclude matching stations |

Multiple filters are space-separated. If any filter matches, the packet passes.

**APRS Position Report Packet Format:**

Raw APRS packets on APRS-IS look like:
```
KD0ABC-9>APRS,TCPIP*:!4456.34N/09315.67W>089/045/comment text
```

Structure: `SOURCE>DESTINATION,PATH:PAYLOAD`

Position report data identifiers:
- `!` — Real-time position, no messaging
- `=` — Real-time position, messaging capable
- `/` — Position with timestamp, no messaging
- `@` — Position with timestamp, messaging capable

Latitude format: `DDMM.hhN` (degrees, minutes, hundredths, N/S)
Longitude format: `DDDMM.hhW` (degrees, minutes, hundredths, E/W)

After the coordinates: `CSE/SPD` (course in degrees / speed in knots)

**Sending APRS Messages:**

Message format injected into APRS-IS:
```
FROMCALL>APRS,TCPIP*::TOCALL   :Message text here{12345
```

- Addressee is padded with spaces to exactly 9 characters
- Message text: 1-67 characters
- Optional message ID after `{` (1-5 alphanumeric chars)
- When message ID is present, the recipient sends ACK

**Acknowledgement format:**
```
TOCALL>APRS,TCPIP*::FROMCALL :ack12345
```

### 1.5 APRS Implementation Recommendations for NewUI

**Phase 1 — aprs.fi API (quick win):**
- Migrate the legacy `do_aprs()` polling logic to the NewUI location API
- Use the existing `location_reports` table with `provider_code = 'aprs'`
- Run as a PHP cron job every 60 seconds
- Batch callsigns in groups of 20 (matching legacy behavior)

**Phase 2 — APRS-IS TCP stream (real-time):**
- Implement a persistent PHP CLI process (or Node.js daemon) that maintains a TCP socket to `rotate.aprs2.net:14580`
- Parse incoming position packets and INSERT into `location_reports`
- Use server-side range filter (`r/lat/lon/dist`) to limit traffic to the agency's area of operations
- Publish position updates via SSE EventBus for real-time map display
- Support sending APRS text messages from the dispatch console

**Phase 3 — Xastir/javAPRSSrvr bridge (optional):**
- For agencies running local APRS infrastructure, support reading positions from a Xastir or javAPRSSrvr MySQL database (as the legacy system does)

---

## 2. OwnTracks Integration Plan

### 2.1 What Is OwnTracks

OwnTracks is an open-source mobile app (iOS and Android) that publishes device location to a private server. Unlike commercial tracking services, OwnTracks data never leaves the agency's infrastructure. This makes it ideal for tracking personnel who carry smartphones but do not have amateur radio equipment.

### 2.2 Communication Modes

OwnTracks supports two communication modes:

| Mode | Protocol | Infrastructure | Complexity |
|------|----------|---------------|------------|
| **MQTT** | MQTT 3.1.1 | Mosquitto broker + Recorder | Higher (but more features) |
| **HTTP** | HTTPS POST | Any web server | Lower (TicketsCAD can be the endpoint directly) |

### 2.3 HTTP Mode (Recommended for TicketsCAD)

In HTTP mode, OwnTracks POSTs JSON payloads directly to a configured URL. This is the simplest integration path because TicketsCAD's existing `api/location.php` endpoint already accepts location reports.

**App Configuration (in OwnTracks app settings):**

| Setting | Value |
|---------|-------|
| Mode | HTTP |
| URL | `https://your-tickets-server/newui/api/location.php?action=report&provider_code=owntracks` |
| Authentication | HTTP Basic (username/password) |
| Tracker ID | 2-character identifier (displayed on map) |
| Monitoring | Significant changes (battery-friendly) or Move mode (continuous) |

**OwnTracks Location JSON Payload (`_type: location`):**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `_type` | string | Yes | Always `"location"` |
| `lat` | float | Yes | Latitude |
| `lon` | float | Yes | Longitude |
| `tst` | int | Yes | Unix timestamp of GPS fix |
| `tid` | string | Yes (HTTP) | 2-char tracker ID |
| `acc` | int | No | Horizontal accuracy (meters) |
| `alt` | int | No | Altitude above sea level (meters) |
| `vac` | int | No | Vertical accuracy (meters) |
| `vel` | int | No | Velocity (km/h) |
| `cog` | int | No | Course over ground (degrees) |
| `batt` | int | No | Battery level (0-100%) |
| `bs` | int | No | Battery status (0=unknown, 1=unplugged, 2=charging, 3=full) |
| `conn` | string | No | Connection type (w=WiFi, o=offline, m=mobile) |
| `t` | string | No | Trigger (p=ping, c=region, u=manual, t=timer, v=visit) |
| `m` | int | No | Monitoring mode (1=significant, 2=move) |
| `inregions` | array | No | Region names device is currently within |
| `p` | float | No | Barometric pressure (kPa) |
| `SSID` | string | No | WiFi network name |
| `created_at` | int | No | Message construction timestamp |

**Optional fields** (`acc`, `alt`, `cog`, `vac`, `vel`) are only included when non-zero.

**HTTP Identification Headers:**
```
Content-Type: application/json
X-Limit-U: [username]
X-Limit-D: [device_name]
```

**Response:** Return `[]` (empty JSON array) for success, or an array of command objects. Supported response types: `location` (friend positions), `cmd` (reportLocation, setWaypoints, etc.), `card` (user info).

**Queueing:** The app automatically queues payloads when the endpoint is unreachable and retries when connectivity resumes.

### 2.4 Other OwnTracks Message Types

| Type | Description | Key Fields |
|------|-------------|------------|
| `waypoint` | Geographic regions / BLE beacons | `desc`, `lat`, `lon`, `rad`, `uuid`, `major`, `minor` |
| `transition` | Enter/leave geofence events | `event` (enter/leave), `desc`, `lat`, `lon`, `wtst` |
| `card` | User identification card | `name`, `face` (base64 PNG), `tid` |
| `cmd` | Remote commands | `action` (reportLocation, dump, status, setWaypoints) |
| `lwt` | Last will and testament (disconnect) | `tst` |
| `encrypted` | Encrypted payload wrapper | Base64-encoded ciphertext |
| `steps` | Pedometer data | Step counts and time ranges |

### 2.5 MQTT Mode (Full-Featured)

MQTT mode provides additional capabilities over HTTP:

- **Presence detection:** Last Will and Testament (LWT) messages notify when a device disconnects
- **Friend tracking:** Devices can see each other's positions via shared MQTT topics
- **Lower latency:** MQTT maintains a persistent connection with push updates
- **Geofence transitions:** Transition events are published immediately
- **Encrypted payloads:** End-to-end encryption support via `_type: encrypted`

**MQTT Topic Structure:**
```
owntracks/<username>/<device>
```

**Infrastructure Required:**
- MQTT broker (Eclipse Mosquitto)
- OwnTracks Recorder (stores and serves location data)
- OwnTracks Frontend (optional web map UI)

### 2.6 OwnTracks Recorder

The OwnTracks Recorder is a lightweight C program that subscribes to MQTT topics and stores location data in a flat-file structure (no external database required). It also serves a built-in web interface for viewing tracks.

**Features:**
- Stores data as JSON files organized by user/device/date
- Built-in HTTP API for querying last positions and historical tracks
- Optional reverse geocoding
- Supports both MQTT and HTTP ingestion (`/pub` endpoint)
- Small footprint (single binary)

**Recorder HTTP Endpoint:**
```
POST https://recorder-host:8083/pub?u=username&d=device
Content-Type: application/json

{"_type":"location","lat":44.97,"lon":-93.26,"tst":1712150400,...}
```

### 2.7 OwnTracks Implementation Recommendations for NewUI

**Option A — Direct HTTP to TicketsCAD (simplest, recommended):**

1. Users configure OwnTracks app in HTTP mode pointing to `https://server/newui/api/location.php`
2. Build a lightweight adapter in `api/location.php` that maps OwnTracks JSON to the `location_reports` table:
   - `lon` maps to `lng`
   - `tst` maps to `reported_at` (convert from Unix timestamp)
   - `vel` maps to `speed`
   - `cog` maps to `heading`
   - `acc` maps to `accuracy`
   - `batt` maps to `battery`
   - `alt` maps to `altitude`
3. The adapter authenticates via HTTP Basic against the TicketsCAD user table
4. No additional infrastructure needed

**Option B — MQTT with Recorder (more features):**

1. Deploy Mosquitto + OwnTracks Recorder via Docker (see Section 5)
2. Build a PHP bridge that polls the Recorder's HTTP API (or subscribes to MQTT) and inserts into `location_reports`
3. Provides presence detection, friend tracking, and the Recorder's web UI
4. More complex but better for agencies tracking many field personnel

---

## 3. Legacy Provider Summary

The legacy system (`tickets/incs/remotes.inc.php`) supports **13 location providers**. The `get_current()` function orchestrates polling all enabled providers. It runs on a 1-minute minimum interval controlled by the `_aprs_time` settings value.

### 3.1 Provider-by-Provider Details

#### APRS (via aprs.fi API)
- **Function:** `do_aprs()`
- **Protocol:** HTTPS REST (aprs.fi JSON API)
- **Poll interval:** 1 minute (shared timer)
- **Auth:** aprs.fi API key stored in `settings.aprs_fi_key`
- **Data received:** lat, lng, speed, course, altitude, time, lasttime, callsign
- **Batching:** Groups callsigns in chunks of 20 per API call
- **Storage:** Updates `responder.lat`, `responder.lng`, `responder.updated` on movement; inserts into `tracks` table with packet_id hash for dedup
- **Movement detection:** Only updates timestamp when lat/lng actually change (via `affected_rows > 0`)
- **Data validation:** `sane()` function checks lat/lng bounds and timestamp age (up to 360 days)

#### InstaMapper
- **Function:** `do_instam()` / `get_instam_device()`
- **Protocol:** HTTP REST (instamapper.com JSON API)
- **Status:** DEFUNCT (InstaMapper shut down circa 2013)
- **Data received:** lat, lng, speed, altitude, heading, device_id
- **Storage:** Updates `responder` table; inserts into `tracks_hh` with `TRACK_INSTAM` source marker; purges tracks older than 7 days

#### GTrack
- **Function:** `do_gtrack()`
- **Protocol:** HTTP (XML API via `data.php?userid=`)
- **Config:** `settings.gtrack_url` stores the server URL
- **Data received:** lat, lng, altitude, mph, kph, heading, local_date, local_time, userid
- **Storage:** Updates `responder`; inserts into both `tracks_hh` (current) and `tracks` (history); purges tracks older than 14 days

#### LocateA
- **Function:** `do_locatea()`
- **Protocol:** HTTP (XML API at `www.locatea.net/data.php?userid=`)
- **Status:** DEFUNCT (LocateA service discontinued)
- **Data received:** lat, lng, altitude, mph, kph, heading, local_date, local_time, userid
- **Storage:** Same pattern as GTrack; uses `sane()` validation

#### Google Latitude
- **Function:** `do_glat()`
- **Protocol:** HTTP (Google Latitude Badge JSON API)
- **Status:** DEFUNCT (Google Latitude shut down August 2013)
- **Data received:** id, lat (coordinates[1]), lng (coordinates[0]), timestamp
- **Storage:** Updates `responder`; inserts into `tracks_hh` and `tracks` on movement

#### OpenGTS
- **Function:** `do_ogts()`
- **Protocol:** HTTP (OpenGTS JSON API at `events/data.jsonx`)
- **Config:** `settings.ogts_info` = `server/account/password` (slash-delimited)
- **Data received:** GPSPoint_lat, GPSPoint_lon, Speed, Timestamp, Address (optional)
- **Special:** Parses address into street/city/state and updates responder address fields; handles both US and UK address formats
- **Storage:** Updates `responder` with lat/lng/address; inserts into `tracks_hh` and `tracks`

#### Internal Tracker (uTrack/BT747/GPSGate)
- **Function:** `do_t_tracker()`
- **Protocol:** HTTP GET (incoming data from devices to `tracker.php`)
- **Architecture:** Push-based (devices POST to TicketsCAD) rather than pull-based
- **Incoming endpoint:** `tracker.php` accepts GET parameters from GPS devices
- **Device support:** GPSGate, BT747, uTrack
- **Data received:** lat, lng, speed, altitude, direction, time, user
- **Storage:** `tracker.php` DELETEs old position from `remote_devices` then INSERTs new one; `do_t_tracker()` reads from `remote_devices` and propagates to `responder` and `tracks`/`tracks_hh`

#### Mobile Tracker
- **Function:** `do_mob_tracker()`
- **Status:** STUB (empty function body)

#### Xastir
- **Function:** `do_xastir()`
- **Protocol:** Direct MySQL connection to Xastir's database
- **Config:** Settings for `xastir_server`, `xastir_db`, `xastir_dbuser`, `xastir_dbpass`
- **Data source:** Queries `simpleStation` table in Xastir DB by callsign
- **Data received:** latitude, longitude, transmit_time
- **Storage:** Updates `responder`; inserts into `tracks` on movement
- **Note:** Opens a separate MySQL connection to the Xastir database

#### FollowMee
- **Function:** `do_followmee()`
- **Protocol:** HTTPS REST (followmee.com JSON API)
- **Config:** `settings.followmee_key` and `settings.followmee_username`
- **API URL:** `https://www.followmee.com/api/tracks.aspx?key=...&function=currentfordevice&deviceid=...`
- **Data received:** DeviceID, Latitude, Longitude, Date, Speed(km/h), Altitude(m)
- **Storage:** Updates `responder`; inserts into `tracks` on movement

#### Traccar
- **Function:** `do_traccar()`
- **Protocol:** Direct MySQL connection to Traccar's database
- **Config:** Settings for `traccar_server`, `traccar_db`, `traccar_dbuser`, `traccar_dbpass`
- **Requirements:** Traccar must be configured to use MySQL (not its default H2 database)
- **Data source:** Queries `tc_devices` for position ID, then `tc_positions` for coordinates
- **Data received:** latitude, longitude, speed, course, altitude, devicetime
- **Storage:** Updates `responder`; inserts into `tracks` on movement
- **Note:** Opens a separate MySQL connection to the Traccar database; track retention configurable via `tracks_length` setting (in hours)

#### javAPRSSrvr
- **Function:** `do_javaprssrvr()`
- **Protocol:** Direct MySQL connection to javAPRSSrvr's dbgate database
- **Config:** Settings for `javaprssrvr_server`, `javaprssrvr_db`, `javaprssrvr_dbuser`, `javaprssrvr_dbpass`
- **Data source:** Queries `APRSPosits` table by callsign
- **Data received:** Latitude, Longitude, Speed, Course, Altitude, ReportTime
- **Storage:** Same pattern as Xastir/Traccar

### 3.2 Database Tables Used by Legacy Providers

| Table | Purpose | Key Columns |
|-------|---------|-------------|
| `responder` | Current unit position (live) | `lat`, `lng`, `updated`, `callsign`, boolean flags per provider (`aprs`, `instam`, `gtrack`, etc.) |
| `tracks` | Historical position trail | `packet_id`, `source`, `latitude`, `longitude`, `speed`, `course`, `altitude`, `packet_date`, `updated` |
| `tracks_hh` | Current position snapshot per device | `source`, `latitude`, `longitude`, `speed`, `altitude`, `course`, `updated`, `utc_stamp`, `from` |
| `remote_devices` | Incoming push-based positions | `lat`, `lng`, `time`, `speed`, `altitude`, `direction`, `user` |
| `settings` | Provider configuration values | Various keys: `aprs_fi_key`, `gtrack_url`, `ogts_info`, `followmee_key`, etc. |

### 3.3 Legacy Provider Status

| Provider | Status | Migrate to NewUI? |
|----------|--------|-------------------|
| APRS (aprs.fi) | Active | Yes (Phase 1) |
| APRS-IS TCP | Not in legacy | Yes (Phase 2, new) |
| OwnTracks | Not in legacy | Yes (new) |
| OpenGTS | Active (self-hosted) | Yes (low priority) |
| Internal Tracker | Active (push-based) | Yes (via location API) |
| Xastir | Active (DB bridge) | Optional |
| FollowMee | Active (commercial) | Low priority |
| Traccar | Active (DB bridge) | Yes (widely used) |
| javAPRSSrvr | Active (DB bridge) | Optional |
| Meshtastic | Not in legacy | Yes (new, already in schema) |
| DMR Radio GPS | Not in legacy | Yes (new, already in schema) |
| InstaMapper | Defunct | No |
| GTrack | Defunct/Rare | No |
| LocateA | Defunct | No |
| Google Latitude | Defunct | No |
| Mobile Tracker | Stub | No |

---

## 4. Recommended Architecture

### 4.1 Priority-Based Provider Resolution

The NewUI `location_providers` table already implements provider prioritization. When multiple providers report positions for the same responder, the system picks the report from the highest-priority (lowest number) enabled provider. This is the correct architecture.

**Recommended priority assignments:**

| Priority | Provider | Rationale |
|----------|----------|-----------|
| 10 | APRS-IS | Most reliable for ham radio operators; sub-second updates |
| 20 | Meshtastic | Growing mesh radio network; good for off-grid |
| 30 | OwnTracks | Smartphone-based; available to all personnel |
| 40 | Internal GPS | Browser-based; requires active web session |
| 50 | DMR Radio | GPS-equipped DMR handhelds |
| 60 | Traccar | General-purpose GPS tracking server |
| 70 | OpenGTS | Legacy fleet tracking |

### 4.2 Ingestion Architecture

The NewUI should support three ingestion patterns:

**Pattern 1 — Poll-based (cron):**
For providers with REST APIs (aprs.fi, FollowMee).
- A PHP CLI script runs every 60 seconds via cron or `setInterval` in a long-running process
- Queries the external API for positions of bound units
- Inserts results into `location_reports`
- Publishes SSE events for real-time map updates

**Pattern 2 — Push-based (HTTP webhook):**
For providers that POST data to us (OwnTracks HTTP, tracker.php, OpenGTS).
- The `api/location.php` endpoint already handles this via `action=report`
- Each provider gets an adapter that maps its native JSON/XML format to the standard fields
- No cron needed; data arrives in real-time

**Pattern 3 — Persistent connection (daemon):**
For providers requiring a long-lived connection (APRS-IS TCP, Meshtastic serial/IP, MQTT).
- A PHP CLI daemon (or future Node.js service) maintains the connection
- Parses incoming packets and inserts into `location_reports`
- Most complex but provides true real-time data

### 4.3 Recommended Implementation Order

1. **OwnTracks HTTP adapter** — Lowest effort; add an adapter in `api/location.php` that maps OwnTracks JSON fields to `location_reports` columns. Can be deployed today with zero infrastructure changes.

2. **APRS aprs.fi poller** — Migrate legacy `do_aprs()` to a NewUI cron script. Uses existing API key. Provides position tracking for all APRS-equipped units.

3. **Browser GPS (Internal)** — Already partially implemented. The app can use the Geolocation API and POST to `api/location.php`.

4. **APRS-IS TCP daemon** — Build a persistent connection to `rotate.aprs2.net:14580` for real-time positions and message sending. This is the biggest win for dispatch operations.

5. **Meshtastic IP/serial bridge** — Connect to a Meshtastic node's HTTP API or serial port to receive mesh network positions.

6. **OwnTracks MQTT (optional)** — Deploy Mosquitto + Recorder for agencies that want presence detection and friend tracking.

### 4.4 NewUI Schema — Already Built

The `location_providers.sql` schema is well-designed for this architecture:

- `location_providers` — Provider definitions with config JSON, enable/disable, priority
- `location_reports` — Unified position history from all providers (lat, lng, altitude, speed, heading, accuracy, battery, raw_data, timestamps)
- `unit_location_bindings` — Maps responder IDs to provider-specific identifiers with per-binding priority

The `api/location.php` endpoint already supports:
- Listing providers (`GET`)
- Querying latest position for a unit (`GET ?unit=X`)
- Querying all bound unit positions (`GET ?all_units=1`)
- Ingesting reports (`POST action=report`)
- Admin config changes (`POST action=save_provider`)
- Binding/unbinding responders (`POST action=bind/unbind`)

### 4.5 What Still Needs Building

| Component | Description | Effort |
|-----------|-------------|--------|
| OwnTracks HTTP adapter | Map OwnTracks JSON to `report` action | Small |
| APRS poller cron | PHP CLI script polling aprs.fi | Small |
| APRS-IS TCP daemon | Persistent socket, packet parser, message sender | Medium |
| SSE integration | Publish location updates to EventBus | Small |
| Map display layer | Leaflet markers/polylines from `all_units` API | Medium |
| Config UI panels | Settings page for each provider | Medium |
| Meshtastic bridge | HTTP/serial adapter | Medium |
| Tracker migration tool | Import legacy `tracks` data | Small |

---

## 5. Docker Compose Example

For agencies wanting OwnTracks MQTT mode with the Recorder:

```yaml
# docker-compose-owntracks.yml
# OwnTracks Recorder + Mosquitto MQTT Broker
# Place in: newui-dev/docker/

version: "3.8"

services:
  mosquitto:
    image: eclipse-mosquitto:2
    container_name: ticketscad-mosquitto
    ports:
      - "1883:1883"    # MQTT
      - "8883:8883"    # MQTT over TLS (configure certs)
    volumes:
      - mosquitto-conf:/mosquitto/config
      - mosquitto-data:/mosquitto/data
      - mosquitto-logs:/mosquitto/log
    restart: unless-stopped

  owntracks-recorder:
    image: owntracks/recorder:latest
    container_name: ticketscad-owntracks
    ports:
      - "8083:8083"    # Recorder web UI + HTTP API
    environment:
      OTR_HOST: mosquitto         # MQTT broker hostname (Docker network)
      OTR_PORT: 1883
      OTR_USER: ""                # Set if Mosquitto requires auth
      OTR_PASS: ""
      OTR_STORAGEDIR: /store
    volumes:
      - owntracks-store:/store
      - owntracks-config:/config
    depends_on:
      - mosquitto
    restart: unless-stopped

volumes:
  mosquitto-conf:
  mosquitto-data:
  mosquitto-logs:
  owntracks-store:
  owntracks-config:
```

**Mosquitto Configuration** (`mosquitto.conf`):
```
listener 1883
allow_anonymous true
# For production, enable authentication:
# password_file /mosquitto/config/passwd
# listener 8883
# certfile /mosquitto/config/certs/server.crt
# keyfile /mosquitto/config/certs/server.key
```

**Usage:**
```bash
docker compose -f docker-compose-owntracks.yml up -d
```

**OwnTracks App Configuration (MQTT mode):**

| Setting | Value |
|---------|-------|
| Mode | MQTT |
| Host | `your-server-ip` |
| Port | `1883` (or `8883` for TLS) |
| Client ID | Auto-generated |
| Topic | `owntracks/<username>/<device>` |
| Username | (match Mosquitto config) |
| Password | (match Mosquitto config) |

**TicketsCAD Bridge Script** (polls Recorder API):
The Recorder exposes a REST API at `http://localhost:8083/api/0/last` that returns the latest position for all tracked devices. A PHP cron job can poll this and insert into `location_reports`.

---

## 6. Data Flow Diagram

```
                              LOCATION DATA SOURCES
    ================================================================

    APRS Radio         OwnTracks App       Meshtastic Mesh     Browser GPS
    (ham operators)     (smartphones)       (LoRa radios)      (dispatchers)
         |                   |                    |                  |
         v                   v                    v                  v
    +-----------+    +---------------+    +---------------+   +------------+
    | aprs.fi   |    | HTTP POST     |    | IP/Serial     |   | Geolocation|
    | REST API  |    | to /api/      |    | connection    |   | API        |
    | -or-      |    | location.php  |    | to node       |   |            |
    | APRS-IS   |    |               |    |               |   |            |
    | TCP:14580 |    +-------+-------+    +-------+-------+   +------+-----+
    +-----+-----+            |                    |                  |
          |                  |                    |                  |
          v                  v                    v                  v
    ================================================================
                    INGESTION LAYER (PHP)
    ================================================================
    |                                                              |
    |   +----------------------------------------------------+    |
    |   |  api/location.php  (action=report)                 |    |
    |   |                                                    |    |
    |   |  - Validates provider_code + unit_identifier       |    |
    |   |  - Validates lat/lng bounds                        |    |
    |   |  - Checks provider is enabled                      |    |
    |   |  - Normalizes fields (speed, heading, battery...)  |    |
    |   |  - Stores raw_data for debugging                   |    |
    |   +------------------------+---------------------------+    |
    |                            |                                |
    ================================================================
                            |
                            v
    ================================================================
                    STORAGE LAYER (MySQL/MariaDB)
    ================================================================
    |                                                              |
    |   +----------------------------------------------------+    |
    |   |  location_reports                                  |    |
    |   |  - id (BIGINT PK)                                  |    |
    |   |  - provider_id (FK to location_providers)          |    |
    |   |  - unit_identifier (callsign/device ID)            |    |
    |   |  - lat, lng (DECIMAL 10,7)                         |    |
    |   |  - altitude, speed, heading, accuracy, battery     |    |
    |   |  - raw_data (original payload)                     |    |
    |   |  - reported_at, received_at                        |    |
    |   +------------------------+---------------------------+    |
    |                            |                                |
    |   +---------------------+  |  +-------------------------+   |
    |   | location_providers  |  |  | unit_location_bindings  |   |
    |   | - code, name        |<-+->| - responder_id          |   |
    |   | - enabled, priority |     | - provider_id           |   |
    |   | - config_json       |     | - unit_identifier       |   |
    |   | - icon, color       |     | - priority, active      |   |
    |   +---------------------+     +-------------------------+   |
    |                                                              |
    ================================================================
                            |
                            v
    ================================================================
                    DISTRIBUTION LAYER
    ================================================================
    |                                                              |
    |   SSE EventBus                    REST API                   |
    |   (real-time push)                (on-demand pull)           |
    |        |                               |                     |
    |   +----+----+                   +------+-------+             |
    |   | event:  |                   | GET ?unit=X  |             |
    |   | location|                   | GET ?all=1   |             |
    |   | _update |                   +--------------+             |
    |   +---------+                                                |
    |        |                               |                     |
    ================================================================
                            |
                            v
    ================================================================
                    DISPLAY LAYER (Browser)
    ================================================================
    |                                                              |
    |   +----------------------------------------------------+    |
    |   |  Leaflet Map                                       |    |
    |   |  - Unit markers with provider-specific colors      |    |
    |   |  - Accuracy circles                                |    |
    |   |  - Speed/heading arrows                            |    |
    |   |  - Track trail polylines                           |    |
    |   |  - Click marker -> unit detail popup               |    |
    |   |  - Geofence boundary polygons                      |    |
    |   +----------------------------------------------------+    |
    |                                                              |
    ================================================================
```

---

## References

- aprs.fi API: https://aprs.fi/page/api
- APRS Protocol Reference (APRS101.PDF): https://www.aprs.org/doc/APRS101.PDF
- APRS-IS Server-Side Filters: https://www.aprs-is.net/javAPRSFilter.aspx
- APRS-IS Connecting: https://pulsemodem.com/pages/connecting-aprs-is/
- APRS Tier 2 Network: https://www.aprs2.net/
- OwnTracks HTTP Mode: https://owntracks.org/booklet/tech/http/
- OwnTracks JSON Format: https://owntracks.org/booklet/tech/json/
- OwnTracks Recorder: https://owntracks.org/booklet/clients/recorder/
- OwnTracks Docker Recorder: https://github.com/owntracks/docker-recorder
- Eclipse Mosquitto: https://mosquitto.org/
