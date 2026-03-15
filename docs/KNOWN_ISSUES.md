# TicketsCAD Known Issues and Documentation Notes

This file tracks UI inconsistencies, undocumented behaviors, and items that need further investigation or user documentation.

---

## Form Validation Issues

### Patient Form (patient_w.php / patient.php)

**Red Asterisk vs. Actual Validation Mismatch**

| Field | Red Asterisk Shown | JS Validated | PHP Validated | Notes |
|-------|--------------------|-------------|---------------|-------|
| Patient ID | Always | Yes | No | Always required client-side, no server-side enforcement |
| Gender | When locale != 1 | When locale != 1 | No | Conditional on system locale setting |
| Insurance | When locale != 1 | **No (commented out)** | No | Code comment says "made non-mandatory" as of 4/8/2014, but asterisk still shows when locale != 1 |

**Action Items:**
- [ ] Remove red asterisk from Insurance field (it is no longer validated)
- [ ] Add PHP-level server-side validation for Patient ID (currently JS-only, bypassable)
- [ ] Consider making asterisk display consistent with actual validation rules
- [ ] Document the `locale` setting's effect on required fields

**Patient ID Purpose:**
The Patient ID field (`frm_name`, maps to `patient.name` column, varchar 32) is a user-entered tracking identifier. It is NOT auto-generated. It serves as a human-assigned label to distinguish between multiple patients on the same incident (e.g., "Patient A", "Patient B", or facility-assigned IDs). The field is checked for duplicates before insert.

---

### Edit Incident Form (edit.php)

**Receiving Facility Field Conditional Rendering** (Fixed in commit 9da0424)
The `frm_rec_facility_id` SELECT element was only rendered when the incident already had a receiving facility assigned. This caused an undefined array key error on form submission when no receiving facility was set.

---

## UI Inconsistencies

### Navigation Bar Pre-Login (Fixed in commit a3ee286)
Navigation buttons were rendered in the HTML source (but CSS-hidden) for unauthenticated visitors. This leaked SOP file paths and other module references in the page source.

### help.php Authentication (Fixed in commit a3ee286)
The Help module page lacked `do_login()` authentication check, allowing unauthenticated access.

---

## Module Documentation Status

Status of user-facing documentation for each module:

| Module | User Guide Section | Admin Guide Section | In-App Help | Status |
|--------|-------------------|--------------------|-----------|----|
| Situation/Main | Draft | Draft | Yes (help.php) | Needs review |
| New Incident | Draft | N/A | Yes | Needs review |
| Edit Incident | Draft | N/A | Partial | Needs detail |
| Units/Responders | Draft | Draft | Yes | Needs review |
| Dispatch/Routes | Draft | N/A | No | **Needs writing** |
| Facilities | Stub | Draft | Yes | Needs detail |
| Patient Records | Draft | N/A | No | **Needs writing** |
| Search | Draft | N/A | Yes | Needs review |
| Reports | Draft | N/A | No | **Needs writing** |
| Configuration | N/A | Draft | Partial | Needs detail |
| SOPs | Stub | Draft | No | Needs detail |
| Chat | Stub | N/A | No | **Needs writing** |
| Messaging | Stub | N/A | No | **Needs writing** |
| Call Board | Stub | N/A | No | **Needs writing** |
| Mobile | Stub | N/A | No | **Needs writing** |
| Personnel | Stub | N/A | No | **Needs writing** |
| ICS Forms | Stub | N/A | No | **Needs writing** |
| Portal | Stub | N/A | No | **Needs writing** |
| Map/Tiles | N/A | Yes (TILE_SETTINGS.md) | No | Good |

---

## Undocumented System Behaviors

### Settings That Affect Form Behavior
- `locale` — Controls whether Gender and Insurance are required on patient forms
- `serial_no_ap` — Controls ticket number display format
- `use_messaging` — Messaging module mode (0=off, 1-3 = various modes)
- `call_board` — Call board mode (0=off, 1=available, 2=always visible)

### Multi-Dispatch Behavior
The `multi` flag on responder units controls whether a unit can be dispatched to multiple incidents simultaneously. When `multi=0`, a unit already assigned to any active incident will have its checkbox disabled on the dispatch screen for other incidents.

### Internet vs Non-Internet Mode
Many PHP files have `_nm` (non-internet/non-maps) variants that render without external map tile dependencies. The mode is determined by the `internet` setting and stored in `$_SESSION['internet']`.

### Ticket Status Values
- **0** — New/Open
- **1** — Closed (records problemend timestamp)
- Other values may be configured via status tables

---

## Database Abstraction Layer Duplication

The system currently runs **two parallel database abstraction layers**:

1. **mysql2i shim** (`incs/mysql2i.class.php`) — A compatibility layer that wraps `mysqli` calls to emulate the legacy `mysql_*` API. Originally introduced to migrate from PHP's removed `mysql_` extension without rewriting all queries.

2. **db.inc.php** (`incs/db.inc.php`) — A newer abstraction providing `db_query()` with automatic prepared statement support, implicit parameter type detection, and `db_fetch_one()` convenience function.

**Current state:**
- Phase 1 security work converted high-risk queries to use `db_query()` with prepared statements
- Many files still use the mysql2i shim via direct `$result->fetch_assoc()` patterns
- Both layers ultimately call the same underlying `mysqli` connection

**Recommended path forward (Phase 3+):**
- Consolidate on `db.inc.php` as the single abstraction layer
- Migrate remaining direct `mysqli` calls to use `db_query()`
- Deprecate and eventually remove the mysql2i shim
- The `db_query()` function's automatic type detection for prepared statements is the preferred pattern going forward

---

## Items Needing Investigation

- [ ] Full audit of all form fields across all modules for required-field indicator accuracy
- [ ] Document all settings table values and their effects
- [ ] Document the dispatch workflow step by step with screenshots
- [ ] Document the portal/service request workflow
- [ ] Investigate and document the ICS forms module
- [ ] Document the APRS and GPS tracking configuration
- [ ] Document the WebSocket server setup (socketserver/)

---

*This file is updated as issues are discovered during the modernization project.*
