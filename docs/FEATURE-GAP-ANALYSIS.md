# Feature Gap Analysis: Legacy TicketsCAD (v3.44) vs NewUI (v4.0)

> **INTERNAL PLANNING DOCUMENT** -- This document is for the TicketsCAD development team only. It is not user-facing documentation and should not be distributed to end users. It tracks the progress of v4.0 NewUI development relative to the v3.44 Legacy system.

Generated: 2026-04-02

---

## 1. Executive Summary

The NewUI v4.0 has achieved substantial parity with the Legacy v3.44 system across core dispatch operations, and in many areas exceeds the legacy system with modern architecture, responsive design, and new capabilities. Of approximately 75 discrete features identified across both systems, roughly 30 are complete or improved in NewUI, 15 are partially implemented (built but needing testing or polish), and 30 remain missing or are in early stages. The most critical gaps are in real-time communications (chat, messaging, SMS), location tracking (APRS, GPS), ICS form generation, and the mobile unit login interface. The NewUI compensates with significant new capabilities not present in legacy: a GridStack dashboard, command bar, SSE real-time updates, two-factor authentication, RBAC schema, equipment/vehicle tracking, scheduling, SOP editor, Zello integration stubs, and a modern responsive design. The path to full parity requires focused work on communications, location services, and a few legacy administrative features.

---

## 2. Feature Comparison Matrix

### 2.1 Dispatch Operations

| Feature | Legacy (v3.44) | NewUI (v4.0) | Gap Status |
|---------|---------------|-------------|------------|
| Situation screen (main dispatch view) | `main.php` — frameset with incident list, unit sidebar, map | `index.php` — GridStack dashboard with 8 widgets | **IMPROVED** |
| New incident form | `add.php` — multi-step form with map | `new-incident.php` — keyboard-first, 8 collapsible sections, protocol panel, geocoding, patients, Ctrl+Enter submit | **IMPROVED** |
| Incident detail/edit | `edit.php` — popup/frame-based edit | `incident-detail.php` — single-page view with notes, assignments, ICS-213 export button | **COMPLETE** |
| Incident list | Embedded in situation screen | `incident-list.php` — dedicated filterable, sortable list | **IMPROVED** |
| Close incident | `close_in.php` — dedicated close action | Via incident detail update API | **COMPLETE** |
| Unit management | `units.php` + `units_nm.php` — map + sidebar list | `units.php`, `unit-detail.php`, `unit-edit.php` — full CRUD | **COMPLETE** |
| Unit status updates | `as_up_un_status.php`, `auto_status.php` — AJAX polling | `api/responder-status.php` + SSE push | **IMPROVED** |
| Responder assignment | `assign_res.php`, `assign_del.php` — frame-based | `api/incident-assign.php` — inline on new-incident and detail pages | **COMPLETE** |
| Call history | `call_hist.php` — popup window | `api/call-history.php` — inline section in new-incident form | **IMPROVED** |
| Situation board (call board) | `board.php` — color-coded elapsed time thresholds per severity | `status.php` — system health focused; no elapsed-time call board equivalent | **PARTIAL** |
| Full-screen situation | `full_scr.php`, `full_sit_scr.php` — dedicated fullscreen map + overlays | `situation.php` — full-screen Leaflet map with semi-transparent incident overlay, SSE refresh | **COMPLETE** |
| Auto-refresh / polling | 5-second AJAX polling cycle (`get_latest_id.php`) | SSE (Server-Sent Events) — push-based, lower latency | **IMPROVED** |
| Major incidents | `maj_inc.php` — group linked incidents with command structure (Gold/Silver/Bronze) | `api/major-incidents.php` — API exists, UI pending | **PARTIAL** |
| Patient management | `patient.php`, `patient_w.php`, `patient_JF.php` — popup patient forms | Inline patient section in new-incident form (add/remove, counter badge) | **COMPLETE** |
| Incident numbering | Base64-encoded config in settings table | Config panel built (`incident-numbers` tab), API pending full implementation | **PARTIAL** |

### 2.2 Mapping

| Feature | Legacy (v3.44) | NewUI (v4.0) | Gap Status |
|---------|---------------|-------------|------------|
| Map display | Google Maps (with fallback to no-map mode via `_NM.php` variants) | Leaflet.js with OSM tiles | **IMPROVED** |
| Geocoding (forward) | Google/Nominatim via settings | Nominatim with viewbox bias, smart city/state correction | **IMPROVED** |
| Geocoding (reverse) | Google/Nominatim | Nominatim reverse with auto cross-street from neighbourhood | **IMPROVED** |
| Tile proxy/caching | `tile_proxy.php`, `get_tiles.php` | `api/weather-proxy.php` + `cache/` directory | **COMPLETE** |
| Map markups (draw on map) | `mmarkup.php` — polygons, lines, circles with categories (region boundary, ring fence, exclusion zone, etc.) | `api/map-markups.php` — API exists with draw/toggle/categories | **PARTIAL** |
| Street view | `street_view.php` — Google Street View popup | Not implemented | **MISSING** |
| Weather overlay | `wx.php`, `wxalert.php`, `wxalert1.php` — weather alerts on map | `api/weather-proxy.php` — weather tile caching proxy for map overlays | **COMPLETE** |
| Road conditions overlay | `road_conditions.php` — alerts with map markers | `api/road-conditions.php` — API exists, config panel built | **PARTIAL** |
| Warn locations (flagged hazards) | `warn_locations.php`, `warnloc_popup.php` — hazard markers on map | Config panel for warn locations exists; `api/proximity-warnings.php` API exists | **PARTIAL** |
| Circle/radius tool | `circle.php` — draw radius on map | Not implemented as standalone; map markups API covers this | **PARTIAL** |

### 2.3 Communications

| Feature | Legacy (v3.44) | NewUI (v4.0) | Gap Status |
|---------|---------------|-------------|------------|
| Built-in chat | `chat.php`, `chat_rd.php`, `chat_wr.php`, `chat_wl.php` — real-time chat via WebSocket/polling | `api/chat.php` + `assets/js/chat-widget.js` — API stubbed, widget JS exists | **PARTIAL** |
| Chat invitations | `chat_invite.php`, `wr_invite.php` — invite users to chat | Not implemented | **MISSING** |
| Internal messaging | `message.php`, `messages.php`, `msg_archive.php` — internal message system with inbox/sent/archive | Not implemented | **MISSING** |
| Email notifications | `do_all_mail.php`, `do_send_mail.php`, `mail.php`, `mail_all.php`, `mail_it.php`, `do_direcs_mail.php` — SMTP-based email per incident/unit/facility | Config panels built (Email Configuration, Email Lists, Notification Rules); delivery engine pending | **PARTIAL** |
| Email lists/groups | `email_lists.php` — manage distribution groups | Config panel for email lists exists | **PARTIAL** |
| SMS messaging | Via `msg_settings` — supports SMS Responder, txtlocal, SMS Broadcast | Config panel for SMS built; delivery engine pending | **PARTIAL** |
| HAS (Hello All Stations) broadcast | `top.php` HAS button — broadcast text to all units | Not implemented | **MISSING** |
| Signals/dispatch codes | `hints_config.php` — admin-managed code table | Config panel for "Field Help Text" exists (signals tab) | **PARTIAL** |
| WebSocket server | `socketserver/` directory — PHP WebSocket server for real-time push | SSE (`api/stream.php`) replaces WebSocket for most use cases | **IMPROVED** |
| Standard messages/templates | `std_msgs` table — canned messages for email/SMS/MotoTRBO | Config panel for "Standard Messages" exists; not yet functional | **PARTIAL** |
| Zello integration | Not present | `api/zello-config.php`, `zello-messages.php`, `zello-token.php`, `zello-user.php` + config panel + widget JS | **NEW** |
| Slack integration | Not present | Config panel exists for Slack | **NEW** |
| Telegram integration | Not present | Config panel exists for Telegram | **NEW** |
| Meshtastic integration | Not present | Config panel exists for Meshtastic mesh networking | **NEW** |
| Webhooks | Not present | `api/webhooks.php` + config panel | **NEW** |

### 2.4 Personnel & Members

| Feature | Legacy (v3.44) | NewUI (v4.0) | Gap Status |
|---------|---------------|-------------|------------|
| Member/personnel database | `member.php` — full member management with custom fields (field1-field65) | `roster.php` — member management with FCC lookup, callsign management, org memberships, comm identifiers | **IMPROVED** |
| Member capabilities | `member_capabilities.php`, `ajax/capabilities_list.php` | Part of roster detail; certifications in config | **COMPLETE** |
| Teams | Not a standalone feature in legacy | `teams.php` — dedicated teams page with NIMS resource typing | **NEW** |
| Training records | `ajax/training_list.php`, `forms/add_training.php`, etc. | `api/training.php` + config panel for training | **COMPLETE** |
| Equipment tracking | `ajax/equipment_list.php`, `forms/add_equipment.php`, etc. — within member records | `equipment.php` — standalone equipment tracking page | **IMPROVED** |
| Vehicle management | `ajax/vehicle_list.php`, `forms/add_vehicle.php`, etc. — within member records | `vehicles.php` — standalone vehicle management page | **IMPROVED** |
| Clothing tracking | `ajax/clothing_list.php`, `forms/add_clothing.php`, etc. | Not implemented as standalone; could be part of equipment | **MISSING** |
| Member file management | `fileman.php`, `forms/add_file.php` | `api/file-upload.php` — file upload API exists | **PARTIAL** |
| Member events | `forms/add_event.php`, `forms/edit_event.php` | Scheduling system covers events | **COMPLETE** |
| Wastebasket (deleted records) | `wastebasket.php`, `ajax/waste_list.php`, `ajax/wb_restore.php`, `ajax/wb_delete.php` — recoverable deletes | Not implemented — deletes are permanent | **MISSING** |
| Organizations | Not present as multi-org | `api/organizations.php` — multi-org support with org switcher in navbar | **NEW** |
| FCC callsign lookup | Not present | `api/callsign-lookup.php` — FCC amateur + GMRS license lookup | **NEW** |
| Zip code lookup | Not present | `api/zipcode-lookup.php` | **NEW** |

### 2.5 Facilities

| Feature | Legacy (v3.44) | NewUI (v4.0) | Gap Status |
|---------|---------------|-------------|------------|
| Facility list/map | `facilities.php`, `facilities_nm.php` — list + map view | `facilities.php` — list view | **COMPLETE** |
| Facility add/edit | `forms/facilities_add_screen.php`, `facilities_edit_screen.php` | `facility-edit.php` — edit page | **COMPLETE** |
| Facility detail | `forms/facilities_view_screen.php` | `facility-detail.php` — detail view | **COMPLETE** |
| Facility board | `facility_board.php` — facility status overview | Not implemented as standalone | **MISSING** |
| Facility categories | `faccategories.php` — manage facility type categories | Config panel for facility types exists | **COMPLETE** |
| Facility routes | `fac_routes.php`, `fac_routes_nm.php` — routing to facilities | Not implemented | **MISSING** |
| Facility status updates | `as_up_fac_status.php` — AJAX status change | `api/facility-detail.php` + `api/facility-save.php` | **COMPLETE** |
| Facility notes | `add_facnote.php` | Via facility detail API | **COMPLETE** |
| Facility bed/capacity | Basic capacity field | `api/facility-capacity.php` — bed tracking with category-based capacity | **IMPROVED** |
| Facility notifications | Per-facility email alerts via `notify` table | Config panel for notification rules exists; delivery pending | **PARTIAL** |

### 2.6 Scheduling

| Feature | Legacy (v3.44) | NewUI (v4.0) | Gap Status |
|---------|---------------|-------------|------------|
| Shift scheduling | Not present | `scheduling.php` — shifts + events with time slots, roles, assignments, self-signup | **NEW** |
| Events management | `forms/add_event.php` — basic event records on members | `api/events.php` + `api/shift-assignments.php` — full event/shift system | **NEW** |

### 2.7 Configuration & Administration

| Feature | Legacy (v3.44) | NewUI (v4.0) | Gap Status |
|---------|---------------|-------------|------------|
| System settings | `config.php` — monolithic config page with inline forms | `settings.php` — hierarchical sidebar with 30+ panels across 8 sections | **IMPROVED** |
| User account management | In `config.php` — add/edit/delete users | Config panel for User Accounts with roles and levels | **IMPROVED** |
| Incident type config | In `config.php` — CRUD for incident types | Config panel with CRUD + regex match patterns + severity + protocols | **IMPROVED** |
| Notification subscriptions | In `config.php` — `notify` table CRUD | Config panel for Notification Rules exists | **COMPLETE** |
| Unit type config | In `config.php` | Config panel for Unit Statuses + `api/unit-types.php` | **COMPLETE** |
| Captions/i18n | `capts.php` — edit 174 UI label overrides in `captions` table | `api/captions.php` — API exists; full i18n string catalog pending | **PARTIAL** |
| Sound/alert settings | In `config.php` — `sound_settings` table | Config panel for Sound/Alerts exists; `assets/js/audio-alerts.js` present | **PARTIAL** |
| Places management | In `config.php` — places table for address shortcuts | Config panel for Places exists | **COMPLETE** |
| Regions management | In `config.php` + `areas_sc.php` + `reset_regions.php` | Config panel for Regions exists | **PARTIAL** |
| Map defaults | In `config.php` | Config panel for Map Defaults + Tile Providers | **COMPLETE** |
| API keys | In `config.php` | Config panel for API Keys | **COMPLETE** |
| Database info/maintenance | `show_tables_and_indexes.php`, `tables.php` | Config panels for Database Info + Backup/Maintenance | **IMPROVED** |
| Server details | `server_details.php` | `status.php` — System Health page with component monitoring | **IMPROVED** |
| SMTP test | `smtp_test.php` | Part of Email Configuration panel | **COMPLETE** |
| Module management | `install_module.php`, `delete_module.php` | Not applicable — NewUI is monolithic by design | **N/A** |
| Quick start guide | `quick_start.php` | Not implemented | **MISSING** |

### 2.8 Reports & Statistics

| Feature | Legacy (v3.44) | NewUI (v4.0) | Gap Status |
|---------|---------------|-------------|------------|
| Reports page | `reports.php` — unit log, station report, dispatch report, after-action, incident management, incident log | `reports.php` + `api/reports.php` — report generation page | **PARTIAL** |
| Report printing | `reports_print.php`, `mdb_print_report.php` | Print stylesheet exists | **PARTIAL** |
| CSV export | `download_report_csv.php` | Via import-export page | **COMPLETE** |
| Report download | `download_report.php` | Via reports page | **PARTIAL** |
| Statistics screen | `stats_scr.php` — customizable dashboard with charts | `api/statistics.php` — statistics API; dashboard widgets show stats | **PARTIAL** |
| City graph | `city_graph.php` — incidents by city chart | Not implemented as standalone | **MISSING** |
| Incident type graph | `inc_types_graph.php` — incidents by type chart | Part of statistics API/widgets | **PARTIAL** |
| Severity graph | `sever_graph.php` — incidents by severity chart | Part of statistics API/widgets | **PARTIAL** |
| BAA chart | `baaChart.php` — before/after action chart | Not implemented | **MISSING** |
| Course report | `course_report.php` — training course report | Part of training API | **PARTIAL** |

### 2.9 ICS Forms

| Feature | Legacy (v3.44) | NewUI (v4.0) | Gap Status |
|---------|---------------|-------------|------------|
| ICS form hub | `ics.php` — form selector with save/archive/email | Not implemented as standalone page | **MISSING** |
| ICS-202 (Objectives) | `ics202.php` | Not implemented | **MISSING** |
| ICS-205 (Radio Comms Plan) | `ics205.php` | Not implemented | **MISSING** |
| ICS-205A (Comms List) | `ics205a.php` | Not implemented | **MISSING** |
| ICS-213 (General Message) | `ics213.php` — create, save to DB, email | `api/winlink-export.php` — Winlink ICS-213 XML export from incident detail | **PARTIAL** |
| ICS-213RR (Resource Request) | `ics213rr.php` | Not implemented | **MISSING** |
| ICS-214 (Activity Log) | `ics214.php` | Not implemented | **MISSING** |
| ICS Positions | Not present | `api/ics-positions.php` — ICS command structure positions | **NEW** |

### 2.10 Mobile

| Feature | Legacy (v3.44) | NewUI (v4.0) | Gap Status |
|---------|---------------|-------------|------------|
| Mobile interface | `mobile.php` + `forms/mobile_screen.php` — simplified mobile dispatch view | Responsive CSS breakpoints; PWA manifest for installation | **PARTIAL** |
| Mobile unit login | Via `top.php` unit login handling | Not implemented | **MISSING** |
| Remote module | `rm/` directory — 40 files for mobile unit operations (chat, alerts, tickets, position updates, mileage) | Not implemented; PWA approach planned | **MISSING** |
| NTP time sync | `ntp2.php` — network time check | Not implemented | **MISSING** |

### 2.11 Security & Authentication

| Feature | Legacy (v3.44) | NewUI (v4.0) | Gap Status |
|---------|---------------|-------------|------------|
| Login/authentication | `index.php` — session-based login with MD5 passwords | `login.php` — session-based with CSRF tokens, day/night theme | **IMPROVED** |
| Access levels | guest/unit/stats/user/admin/super in `config.php` | Roles & Levels config panel; RBAC schema exists (`api/rbac.php`) | **IMPROVED** |
| Two-factor auth | Not present | `api/tfa.php` + `assets/js/profile-tfa.js` + QR code library; config panel for 2FA | **NEW** |
| Audit logging | `log.php`, `logs.php` — basic log viewer | `api/audit-log.php` — comprehensive audit log with categories, severity, user filtering; `api/service-uptime.php` for health monitoring | **IMPROVED** |
| Field encryption | Not present | `api/login-security.php` + `assets/js/field-encrypt.js` — client-side encryption for non-HTTPS | **NEW** |
| Login security settings | Basic session timeout | Config panels for Login Settings + Two-Factor Auth + Field Encryption | **IMPROVED** |
| Session management | PHP sessions with cookie expiration (`set_cook_exp.php`) | PHP sessions with CSRF tokens | **IMPROVED** |
| Compliance | Not present | `api/compliance.php` — compliance API | **NEW** |

### 2.12 Import / Export

| Feature | Legacy (v3.44) | NewUI (v4.0) | Gap Status |
|---------|---------------|-------------|------------|
| MDB import | `import_mdb.php`, `ticketsmdb_import.php` — Access DB import | `api/legacy-import.php` — legacy data import API | **COMPLETE** |
| CSV export | `download_report_csv.php` | `import-export.php` page with `api/import-export.php` | **COMPLETE** |
| XML interface | `external.php` — XML feed of open incidents/units | Not implemented | **MISSING** |
| DB loader | `db_loader.php`, `loader.php`, `load.php` — bulk data loading | Via import-export API | **PARTIAL** |
| Constituents import/export | Not present as dedicated feature | `api/constituents-import.php`, `api/constituents-export.php` | **NEW** |

### 2.13 Other Features

| Feature | Legacy (v3.44) | NewUI (v4.0) | Gap Status |
|---------|---------------|-------------|------------|
| Board/call board view | `board.php` — color-coded dispatch overview with elapsed time thresholds | `status.php` is system health, not a call board; no equivalent dispatch board | **MISSING** |
| Full screen map | `full_scr.php` — map-only fullscreen | `situation.php` — fullscreen map with overlay | **IMPROVED** |
| Links panel | `top.php` Links button — configurable external links | Not implemented | **MISSING** |
| Help system | `help.php` — built-in help documentation | Not implemented | **MISSING** |
| About page | `about.php` | Not implemented | **MISSING** |
| SOP viewer | SOP button opens external URL | `sop.php` — built-in SOP viewer/editor with revisions | **IMPROVED** |
| Contacts/constituents | `contact.php` — basic contact DB | `constituents.php` — full contact management with import/export | **IMPROVED** |
| Search | `search.php` — search by various criteria | `search.php` + `api/incident-search.php` | **COMPLETE** |
| Print screen | `print_screen.php` — print-friendly view | Print stylesheet exists | **COMPLETE** |
| Day/night themes | CSS swap via `do_day_night_swap.php`, `stylesheet.php` | Bootstrap `data-bs-theme` toggle with persistent preference | **IMPROVED** |
| Location tracking (APRS) | `do_aprs.php`, `get_php_aprs.php` — APRS position ingestion | Config panels for Location Providers + Provider Settings exist; ingestion engine pending | **PARTIAL** |
| Location tracking (OpenGTS) | `opengts.php` — OpenGTS integration | Planned in backlog | **MISSING** |
| Location tracking (internal) | `tracker.php`, `track_u.php`, `tracks.php`, `tracks_hh.php` — built-in tracking | Schema + provider config exists; real-time display pending | **PARTIAL** |
| Geofencing | `ajax/check_ringfence.php`, `ajax/check_exclzone.php` — ring fence and exclusion zone checks | `api/geofences.php` — geofencing API exists | **PARTIAL** |
| On-scene watch | `os_watch.php` — popup timer for unit time-on-scene | Not implemented | **MISSING** |
| WebSocket monitor | `ws_monitor.php` — WebSocket connection monitor | SSE connection indicator in navbar | **IMPROVED** |
| Docker/auto-install | `docker-autoinstall.php`, `install.php` | Not implemented | **MISSING** |
| Portal / service requests | `portal/` — 7 files for public-facing service request submission | Not implemented | **MISSING** |
| Command bar | Not present | `assets/js/command-bar.js` — `/` prefix command input for rapid dispatch | **NEW** |
| Dashboard widgets | Not present | `index.php` — GridStack draggable/resizable widgets with layout save/undo | **NEW** |
| SSE real-time updates | Not present (used polling + WebSocket) | `api/stream.php` — Server-Sent Events for push updates | **NEW** |
| Keyboard navigation | Basic tabindex on buttons | `assets/js/keyboard-nav.js` — comprehensive keyboard-first design | **NEW** |
| Profile page | In `config.php` — edit own profile | `profile.php` — dedicated profile page with password change + 2FA | **IMPROVED** |

### 2.14 Location Tracking

| Feature | Legacy (v3.44) | NewUI (v4.0) | Gap Status |
|---------|---------------|-------------|------------|
| APRS tracking | `do_aprs.php`, `get_php_aprs.php` — poll APRS-IS for positions | Config panel + schema exists; ingestion not yet running | **PARTIAL** |
| Google Latitude | `latitude.php`, `locatea.php`, `gtrack.php` — Google Latitude integration | Deprecated; OwnTracks planned as replacement | **N/A** |
| OpenGTS | `opengts.php` — OpenGTS server integration | Planned in backlog | **MISSING** |
| uTrack | `tracker.php` — Windows Mobile tracker | Obsolete technology | **N/A** |
| Meshtastic | Not present | Config panel exists; integration planned | **NEW** |
| OwnTracks | Not present | Planned in backlog | **NEW** |
| DMR GPS | Not present | Config panel exists; integration planned | **NEW** |

---

## 3. Features Complete in NewUI

The following features are fully functional in NewUI v4.0:

**Core Dispatch:**
- Dashboard with 8 GridStack widgets (draggable, resizable, layout save/undo)
- New incident form (keyboard-first, 8 collapsible sections, protocol panel, geocoding, patients, responders, Ctrl+Enter)
- Incident detail view with notes, assignments, navigate + ICS-213 export buttons
- Incident list with filtering and sorting
- Unit list, detail, and edit views
- Responder assignment with search filter
- Call history search (phone/address)
- Full-screen situation view with Leaflet map and SSE auto-refresh

**Personnel & Assets:**
- Roster/personnel with FCC lookup, callsign management, org memberships, comm identifiers
- Teams page with NIMS resource typing
- Equipment tracking (standalone page)
- Vehicle management (standalone page)
- Training records management

**Facilities:**
- Facility list, detail, edit views
- Facility types configuration
- Facility capacity/bed tracking API

**Other Complete Pages:**
- Login with day/night theme and CSRF
- Search page
- Contacts/constituents with import/export
- Import/export page
- SOP viewer/editor with revisions
- Scheduling (shifts, events, time slots, roles, assignments, self-signup)
- Profile page with password change and 2FA enrollment
- System health/status page

**Configuration (30+ panels):**
- System Settings, API Keys, Lookup Services, Database Info, Backup/Maintenance
- Incident Types, Severity Levels, Field Help Text, Unit Statuses, Facility Types
- Display Settings, Sound/Alerts, Incident Numbers
- User Accounts, Roles & Levels, Login Settings, Two-Factor Auth, Field Encryption
- Organizations, Members/Personnel, Teams, Certifications, ICS Positions
- Equipment Types, Vehicle Types, Training, Member Statuses, Member Types
- Notification Rules, Email Config, Email Lists, SMS Config, Telegram, Slack
- Radio Messaging, Comm/Location Modes, Zello, Meshtastic, Webhooks
- Standard Messages, Chat Settings
- Facilities, Regions, Places, Warn Locations, Constituents, Road Conditions
- Map Defaults, Tile Providers
- Location Providers, Provider Settings, Geofencing

**APIs (60+):**
- Full JSON API layer for all features listed above
- SSE stream for real-time updates
- Audit logging with service health monitoring

---

## 4. Features Missing from NewUI

The following legacy features have no equivalent in NewUI v4.0:

### Critical (needed for dispatch operations)
1. **Dispatch call board** (`board.php`) -- color-coded elapsed-time board showing incident status progression. The legacy board uses severity-based time thresholds to highlight overdue status changes in red. NewUI's `status.php` is a system health page, not a dispatch board.
2. **Internal messaging system** (`message.php`, `messages.php`, `msg_archive.php`) -- inbox/sent/archive messaging between operators.
3. **Chat system** (real-time delivery) -- Legacy has working WebSocket chat; NewUI has API stubs and widget JS but no working real-time chat delivery.
4. **HAS broadcast** -- "Hello All Stations" one-click broadcast to all connected units.
5. **Mobile unit login** -- Sign in as a specific unit/apparatus with customized mobile interface.
6. **Remote mobile module** (`rm/` directory) -- 40+ files providing mobile operators with incident lists, status buttons, position reporting, mileage tracking, chat, and alerts.

### Important (ICS compliance, operational features)
7. **ICS-202** (Incident Objectives) form
8. **ICS-205** (Radio Communications Plan) form
9. **ICS-205A** (Communications List) form
10. **ICS-213RR** (Resource Request) form
11. **ICS-214** (Activity Log) form
12. **ICS form hub** -- Centralized form management (create, save to DB, archive, email)
13. **On-scene watch** (`os_watch.php`) -- Timer popup tracking how long units have been on scene.
14. **Facility board** (`facility_board.php`) -- Overview of all facility statuses.
15. **Facility routing** (`fac_routes.php`) -- Route calculation to facilities.
16. **Street View** (`street_view.php`) -- Google Street View integration.

### Administrative / Nice-to-Have
17. **Wastebasket** (recoverable deletes) -- Legacy allows deleted records to be restored.
18. **XML interface** (`external.php`) -- XML feed for external system integration.
19. **Links panel** -- Configurable external links accessible from toolbar.
20. **Help system** (`help.php`) -- Built-in help documentation.
21. **About page** (`about.php`) -- Version info and credits.
22. **Quick start guide** (`quick_start.php`)
23. **Portal / service requests** (`portal/`) -- Public-facing service request submission (7 files).
24. **Clothing tracking** -- Uniform/clothing inventory per member.
25. **City graph** -- Standalone incidents-by-city chart.
26. **BAA chart** -- Before/After Action chart.
27. **Docker/auto-install** (`docker-autoinstall.php`, `install.php`)
28. **OpenGTS integration** -- GPS tracking server integration.

---

## 5. Features Improved in NewUI

These features exist in both systems but are significantly better in NewUI:

| Feature | What Changed |
|---------|-------------|
| **Situation screen** | Frameset replaced by GridStack dashboard with draggable/resizable widgets and per-user layout persistence |
| **New incident form** | Keyboard-first design with tabindex flow, 8 collapsible sections, inline protocol panel, smart geocoding with city/state correction, patient management, responder assignment with search |
| **Map** | Google Maps dependency removed; replaced with Leaflet.js + OSM (free, no API key required) |
| **Geocoding** | Nominatim with viewbox bias, smart city/state auto-correction, cross-street from neighbourhood data, city text selected for instant overwrite after geocode |
| **Real-time updates** | 5-second AJAX polling replaced with SSE (Server-Sent Events) for lower latency push updates |
| **Configuration** | Monolithic single-page config replaced with hierarchical sidebar navigation across 30+ organized panels |
| **Day/night theme** | CSS file swap replaced with Bootstrap 5 `data-bs-theme` toggle supporting proper dark mode throughout |
| **Personnel management** | Basic member records enhanced with FCC callsign lookup, communication identifiers, organization memberships, and dedicated equipment/vehicle pages |
| **Unit management** | Frame-based popups replaced with dedicated list/detail/edit pages |
| **Incident detail** | Frame-based edit replaced with single-page view including inline notes, assignment panel, and ICS-213 export |
| **Security** | MD5 passwords and no CSRF replaced with CSRF tokens on all POST endpoints, session hardening, and available 2FA |
| **Audit logging** | Basic log viewer replaced with comprehensive filterable audit log with categories, severity levels, and user tracking |
| **Server monitoring** | Simple server details replaced with real-time component health monitoring (DB, PHP, OS, disk, sessions, cache) |
| **SOP** | External URL link replaced with built-in viewer/editor with revision tracking |
| **Contacts** | Basic contact database replaced with full constituent management including import/export capabilities |
| **Facility capacity** | Simple capacity field replaced with category-based bed tracking (ICU, ER, shelter cots, etc.) |

---

## 6. New Features Only in NewUI

These capabilities do not exist in the legacy system:

| Feature | Description |
|---------|-------------|
| **GridStack dashboard** | Draggable, resizable widget grid with per-user layout save/undo snapshots |
| **Command bar** | `/`-prefix rapid dispatch input for keyboard-first operation |
| **SSE real-time stream** | Push-based updates via Server-Sent Events (replaces polling) |
| **Two-factor authentication** | TOTP 2FA with QR code enrollment, backup codes, admin enforcement |
| **Field encryption** | Client-side public-key encryption for non-HTTPS deployments |
| **RBAC schema** | Role-based access control schema (roles, permissions, role_permissions) |
| **Teams with NIMS typing** | Dedicated teams management with NIMS resource type classification |
| **Scheduling system** | Shift scheduling with events, time slots, roles, assignments, and self-signup |
| **Equipment tracking** | Standalone asset tracking page (not just per-member) |
| **Vehicle management** | Standalone vehicle management page |
| **SOP editor** | Built-in Standard Operating Procedures viewer/editor with revision history |
| **Organizations** | Multi-organization support with org switcher in navbar |
| **FCC callsign lookup** | Automated FCC amateur + GMRS license lookup |
| **Zip code lookup** | Address auto-fill from zip code |
| **Constituents import/export** | CSV import/export for contact database |
| **Zello integration** | Network radio integration stubs (config, messages, token, user APIs) |
| **Slack integration** | Notification channel configuration |
| **Telegram integration** | Notification channel configuration |
| **Meshtastic integration** | Mesh networking configuration for LoRa devices |
| **Webhooks** | Event-driven webhook configuration for external integrations |
| **Compliance API** | Compliance monitoring endpoint |
| **Service health monitoring** | Real-time uptime tracking with component-level health checks |
| **Keyboard navigation system** | Comprehensive keyboard-first navigation across all pages |
| **Print stylesheet** | CSS print layout for all pages |
| **PWA manifest** | Progressive Web App installable from mobile browsers |
| **Responsive CSS** | Mobile-responsive design across all pages |
| **50 demo incident types** | Pre-loaded incident types with protocols across 5 org templates (RACES, CERT, Med Team, Vol Fire, Campus PD) |
| **ICS positions** | ICS command structure position management |
| **Winlink ICS-213 XML export** | Generate Winlink-compatible XML for amateur radio form transmission |
| **Geofencing API** | Define geographic boundaries with entry/exit alerts |
| **Major incidents API** | Link/unlink incidents with command structure (API layer) |
| **Map markups API** | Draw, toggle, categorize map overlays (API layer) |
| **Road conditions API** | Road condition reporting with map overlay (API layer) |
| **File upload API** | Attach files to incidents, members, facilities |
| **Location providers schema** | Support for APRS, Meshtastic, OwnTracks, DMR, Zello location sources |
| **Legacy import API** | Dedicated endpoint for importing data from v3.44 installations |

---

## 7. Gap Closure Plan

Prioritized by operational impact. Effort estimates assume a single developer.

### Priority 1: Critical for Dispatch Operations (Weeks 1-6)

| # | Feature | Effort | Notes |
|---|---------|--------|-------|
| 1 | **Dispatch call board** | 3-5 days | Build a `board.php` or dashboard widget showing color-coded incident status progression with elapsed-time thresholds per severity. This is a core dispatch tool. |
| 2 | **Chat system (real-time delivery)** | 5-7 days | Wire up the existing `chat-widget.js` and `api/chat.php` with SSE or WebSocket delivery. Channels: all, by incident, direct message. |
| 3 | **Internal messaging** | 3-5 days | Build inbox/sent/archive messaging between operators. Can leverage the existing notification infrastructure. |
| 4 | **HAS broadcast** | 1 day | Add a broadcast button to the navbar that sends a text message to all connected users via SSE. |
| 5 | **On-scene watch** | 1-2 days | Timer component showing elapsed time per unit on scene. Could be a dashboard widget or popup. |

### Priority 2: ICS Compliance (Weeks 4-8)

| # | Feature | Effort | Notes |
|---|---------|--------|-------|
| 6 | **ICS form hub page** | 2-3 days | Central page for creating, viewing, archiving, and emailing ICS forms. Database table already exists in legacy. |
| 7 | **ICS-213 full implementation** | 2-3 days | Extend beyond XML export to include create/edit/save/email. Reference legacy `ics213.php`. |
| 8 | **ICS-214 (Activity Log)** | 2-3 days | Auto-generate from incident action log data. |
| 9 | **ICS-205/205A (Radio Comms)** | 2-3 days | Template forms for radio communications plan and list. |
| 10 | **ICS-202 (Objectives)** | 1-2 days | Template form for incident objectives. |
| 11 | **ICS-213RR (Resource Request)** | 1-2 days | Template form for resource requests. |

### Priority 3: Mobile (Weeks 6-12)

| # | Feature | Effort | Notes |
|---|---------|--------|-------|
| 12 | **Mobile unit login** | 3-5 days | Sign in as a specific unit; show unit-appropriate interface. |
| 13 | **Mobile dispatch view** | 5-7 days | Purpose-built mobile views for field units: status buttons, incident details, GPS position reporting. PWA service worker for offline. |
| 14 | **Push notifications** | 2-3 days | Web Push API for incident alerts and assignments. |

### Priority 4: Communication Delivery (Weeks 8-14)

| # | Feature | Effort | Notes |
|---|---------|--------|-------|
| 15 | **Email notification delivery** | 3-5 days | Connect the existing config panels to an actual SMTP delivery engine. |
| 16 | **SMS delivery engine** | 3-5 days | Connect SMS config to a provider (Twilio/BulkVS/generic REST). |
| 17 | **Signals/dispatch codes (functional)** | 1-2 days | Make the Field Help Text config panel fully functional with quick-send from dispatch. |

### Priority 5: Location Tracking (Weeks 10-16)

| # | Feature | Effort | Notes |
|---|---------|--------|-------|
| 18 | **APRS position ingestion** | 3-5 days | Implement APRS-IS connection to receive real-time positions. Schema and config panels exist. |
| 19 | **Real-time unit positions on map** | 2-3 days | Display unit locations on dashboard map with auto-refresh. |
| 20 | **Meshtastic integration** | 3-5 days | Receive GPS positions via Meshtastic mesh network. |

### Priority 6: Administrative Features (Weeks 12-18)

| # | Feature | Effort | Notes |
|---|---------|--------|-------|
| 21 | **Wastebasket (recoverable deletes)** | 2-3 days | Soft-delete pattern with restore capability. |
| 22 | **Facility board** | 1-2 days | Overview page or widget showing all facility statuses. |
| 23 | **Help system** | 2-3 days | Built-in help documentation. Could be markdown-based like SOP. |
| 24 | **Portal / service requests** | 3-5 days | Public-facing form for community members to submit service requests. |
| 25 | **RBAC enforcement** | 5-7 days | Schema exists; wire up permission checks in all API endpoints and UI. |
| 26 | **Major incidents UI** | 3-5 days | API exists; build the UI for linking incidents and managing command structure. |
| 27 | **Map markups UI** | 3-5 days | API exists; build drawing tools UI on the map. |
| 28 | **Captions/i18n** | 3-5 days | Full string catalog with admin override capability. |

### Priority 7: Nice-to-Have (Weeks 16+)

| # | Feature | Effort | Notes |
|---|---------|--------|-------|
| 29 | Links panel | 0.5 days | Configurable external links in navbar dropdown |
| 30 | About page | 0.5 days | Version info, credits, license |
| 31 | XML interface | 1-2 days | External XML feed (may be replaced by webhooks/API) |
| 32 | Facility routing | 2-3 days | Route calculation using Leaflet routing |
| 33 | Street View | 1 day | Link to Google Street View from incident/facility detail |
| 34 | Docker/installer | 3-5 days | Automated installation script |
| 35 | Clothing tracking | 1-2 days | Could be a category under equipment |
| 36 | Statistical charts | 2-3 days | City graph, severity graph, BAA chart |

### Estimated Total Effort

| Priority | Timeframe | Days |
|----------|-----------|------|
| P1: Critical dispatch | Weeks 1-6 | 13-20 days |
| P2: ICS compliance | Weeks 4-8 | 10-16 days |
| P3: Mobile | Weeks 6-12 | 10-15 days |
| P4: Communication delivery | Weeks 8-14 | 7-12 days |
| P5: Location tracking | Weeks 10-16 | 8-13 days |
| P6: Administrative | Weeks 12-18 | 19-30 days |
| P7: Nice-to-have | Weeks 16+ | 11-17 days |
| **Total** | **~18 weeks** | **78-123 days** |

Note: Many Priority 6 and 7 items have partial implementations (APIs exist, config panels built) which significantly reduces the remaining effort. The estimates above account for this.
