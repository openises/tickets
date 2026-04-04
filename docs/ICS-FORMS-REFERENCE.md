# ICS Forms Reference -- Legacy TicketsCAD v3.44

Research document for informing the NewUI v4.0 ICS forms implementation.

---

## 1. Overview

The legacy codebase has **two generations** of ICS form implementations:

### Generation 1 -- Root-level files (2012-2014)
Simple email-only forms. No database persistence. Located directly in `tickets/`.

| File | Form | Created |
|------|------|---------|
| `ics213.php` | ICS-213 General Message | 3/22/2012 |
| `ics202.php` | ICS-202 Incident Objectives | 2/26/2014 |
| `ics205.php` | ICS-205 Radio Communications Plan | 2/26/2014 |
| `ics205a.php` | ICS-205A Communications List | 2/26/2014 |
| `ics213rr.php` | ICS-213RR Resource Request | 10/4/2014 |
| `ics214.php` | ICS-214 Activity Log | 2/26/2014 |

### Generation 2 -- `ics/` subdirectory (2017+)
Full-featured forms with database save/load, archiving, and email. Managed through `ics.php` hub page.

| File | Form | Title Constant |
|------|------|----------------|
| `ics/ics213.php` | ICS-213 | General Message |
| `ics/ics202.php` | ICS-202 | Incident Objectives |
| `ics/ics205.php` | ICS-205-CG | Incident Radio Communications Plan |
| `ics/ics205a.php` | ICS-205A | Communications List |
| `ics/ics206.php` | ICS-206 | Medical Plan |
| `ics/ics213rr.php` | ICS-213RR | Resource Request |
| `ics/ics214.php` | ICS-214 | Activity Log |
| `ics/ics214a.php` | ICS-214a | Individual Log |
| `ics/ics221.php` | ICS-221 | Demobilization Check-Out |

### Supporting Files
| File | Purpose |
|------|---------|
| `ics.php` | Hub page -- lists saved forms, offers "New" buttons for each form type |
| `ics/ics.css.php` | Shared CSS variables (PHP file, not actual CSS) used by all Gen 2 forms |
| `ics/balloon.css` | Tooltip library (CSS-only tooltips using `data-balloon` attribute) |

---

## 2. Common Patterns

### 2.1 Generation 1 Pattern (Root-Level)

All root-level forms follow an identical three-step workflow controlled by a `$step` switch:

1. **Step 0 (ics213.php only) / Step 1**: Contact address selection
   - Queries `contacts` table for email addresses
   - Displays checkboxes for each contact (email, mobile, other fields)
   - Builds pipe-delimited (`|`) address string in hidden field `frm_add_str`
   - Buttons at bottom select which ICS form to fill out

2. **Step 1 (ics213.php) / Case 1**: Form entry
   - Displays the ICS form layout as an HTML table with input fields
   - Auto-populates current user name (from `user` table), date, time
   - Uses helper functions `in_str()`/`in_text()` and `in_text()`/`in_area()` to generate input HTML
   - Client-side validation via `validate()` function
   - Buttons: Reset, Cancel (returns to step 0), Send/Submit

3. **Step 2**: Email sending
   - Calls `html_mail()` to send form as HTML email
   - Displays confirmation message
   - Window auto-closes after 3.5-5 seconds

**Key characteristics:**
- No database persistence -- form data exists only in POST and the sent email
- No save/reload capability
- No archiving
- No incident linkage
- PHP native `mail()` function only

### 2.2 Generation 2 Pattern (ics/ Subdirectory)

All Gen 2 forms share identical scaffolding with a `$func` switch controlling operations:

| Func | Operation | Description |
|------|-----------|-------------|
| `c` | Create | Display empty form with "Save to DB" and "Send" buttons |
| `c2` | Create Step 2 | INSERT form data into `ics` table |
| `u` | Update/Edit | Load saved form from DB, display with edit controls |
| `u2` | Update Step 2 | UPDATE form data in `ics` table |
| `m` | Mail Step 1 | Save if dirty, then show contact address picker |
| `m2` | Mail Step 2 | Send HTML email to selected contacts, increment count |
| `a` | Archive | Set `archived` timestamp on the form record |
| `e` | De-archive | Clear `archived` timestamp (set NULL) |
| `d` | Delete | DELETE form record from DB (only allowed for archived forms) |

**Shared infrastructure:**
- `ics.css.php` included by all forms -- provides CSS variable strings for inline styles
- Common helper functions: `in_text()`, `in_area()`, `in_check()`, `set_input_strings()`, `merge_template()`
- `get_name()` function to retrieve the form name from DB
- `can_edit()` function controls whether fields are editable (uses `onfocus='this.blur()'` to block)
- Session validation -- redirects if no session
- Payload stored as base64-encoded JSON in the `ics` table
- Dirty tracking via hidden `dirty` field and `onchange` handlers
- Parameterized queries using `db_query()` with `?` placeholders
- Input sanitization via `sanitize_int()` and `sanitize_string()`

### 2.3 The `html_mail()` Function

Identical implementation copied into every form file (both generations). Not shared via include.

```
function html_mail($to, $subject, $html_message, $from_address, $from_display_name='') {
    $from = get_variable('email_from');
    $from = is_email($from) ? $from : "no-reply@ticketscad.com";
    $headers = "From: {$from_display_name}<{$from}>\n";
    $headers .= 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
    $temp = get_variable('email_reply_to');
    if (is_email($temp)) {
        $headers .= "Reply-To: {$temp}\r\n";
    }
    $temp = @mail($to, $subject, $html_message, $headers);
}
```

**Configuration sources:**
- `email_from` setting -- sender address (falls back to `no-reply@ticketscad.com`)
- `email_reply_to` setting -- optional reply-to address
- `title_string` setting -- used as display name, truncated to 30 chars

**Limitations:**
- Uses PHP native `mail()`, not PHPMailer
- No error handling on send failure (return value ignored)
- ISO-8859-1 charset hardcoded
- No attachment support

### 2.4 The `ics` Database Table

Created automatically by `ics.php` on first access. MyISAM engine.

```sql
CREATE TABLE `ics` (
  `id` bigint(8) NOT NULL AUTO_INCREMENT,
  `to` varchar(256) DEFAULT NULL COMMENT 'comma separated, 0 = all',
  `name` varchar(256) NOT NULL COMMENT 'form name',
  `type` varchar(64) NOT NULL COMMENT 'form type (e.g., ICS 213)',
  `script` varchar(24) NOT NULL COMMENT 'php script filename',
  `payload` varchar(10000) DEFAULT NULL COMMENT 'form data as JSON (base64 encoded)',
  `count` int(3) NOT NULL DEFAULT 0 COMMENT 'times sent',
  `_by` int(7) NOT NULL COMMENT 'user id of last editor',
  `_from` varchar(16) NOT NULL COMMENT 'IP address',
  `_as-of` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'updated on',
  `_sent` timestamp NULL DEFAULT NULL COMMENT 'last sent on',
  `archived` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM;
```

**Data storage approach:**
- All form field data stored as a single JSON blob (base64 encoded) in `payload`
- The `name` column stores a truncated form name + "/" + record ID for uniqueness
- `type` stores the form constant (e.g., "ICS 213", "ICS 205-CG")
- `script` stores the PHP filename for routing back to the correct form handler

### 2.5 Access Control

- **Gen 1**: No access control. Relies on the legacy session being active.
- **Gen 2**: Uses `can_edit()` function. If user cannot edit, all input fields get `onfocus='this.blur()'` which prevents typing. The form is still visible but read-only. `can_edit()` returns true for admins, for users if `oper_can_edit=1` setting, or for units if `unit_can_edit=1` setting.

### 2.6 Print Capability

- **Neither generation has dedicated print CSS or print pages.**
- Forms are rendered as HTML tables with inline styles sized in inches (e.g., `width: 8.5in`).
- Browser File > Print would produce a reasonable output since table widths target US Letter page size.
- Gen 2 forms display inputs as `<span>` text (read-only) when in email/print mode (`func == "m2"`).

### 2.7 Relationship to Incidents

- **Gen 1**: No incident linkage whatsoever. Forms are standalone.
- **Gen 2**: Forms have an optional "Incident Name" field (manual text entry). No foreign key to the `ticket` table. No auto-population from incidents. The forms operate independently of the incident management system.

---

## 3. Detailed Form Specifications

### 3.1 ICS-213 General Message

The most commonly used form. Has both Gen 1 and Gen 2 implementations.

#### Gen 1 (`ics213.php`)

**Fields (14 items):**

| # | Field | Type | Size | Required | Default |
|---|-------|------|------|----------|---------|
| 1 | To | text | 36 | Yes | empty |
| 2 | Position (To) | text | 36 | No | empty |
| 3 | From | text | 36 | Yes | Current user name |
| 4 | Position (From) | text | 36 | No | empty |
| 5 | Subject | text | 36 | Yes | empty |
| 6 | Date | text | 16 | No | Current date |
| 7 | Time | text | 12 | No | Current time |
| 8 | Message | textarea | 90x4 | Yes | empty |
| 9 | Signature | text | 36 | No | Current user name |
| 10 | Position | text | 32 | No | empty |
| 11 | Reply | textarea | 90x4 | No | empty |
| 12 | Reply Date | text | 16 | No | empty |
| 13 | Reply Time | text | 8 | No | empty |
| 14 | Signature/Position (Reply) | text | 34 | No | empty |

**Validation:** To, From, Subject, and Message are required (client-side JS).
**Template function:** `template_213_t()` returns table-only HTML; `template_213()` wraps in full page.
**Email subject:** "ICS-213 Message - {subject field value}"

#### Gen 2 (`ics/ics213.php`)

**Fields (18 items):**

| Key | Field | Type | Size | Required | Default |
|-----|-------|------|------|----------|---------|
| f_0 | Incident Name | text | 62 | No | empty |
| f_1 | To (Name) | text | 28 | No | empty |
| f_2 | To (Position) | text | 40 | No | empty |
| f_3 | From (Name) | text | 28 | No | empty |
| f_4 | From (Position) | text | 40 | No | empty |
| f_5 | Subject | text | 28 | Yes (for save) | empty |
| f_6 | Date | text | 10 | No | Current date |
| f_7 | Time | text | 6 | No | Current time |
| f_8 | Message | textarea | 84x4 | No | empty |
| f_9 | Approved by (Name) | text | 28 | No | Current user name |
| f_10 | Approved by (Signature) | text | 24 | No | empty |
| f_11 | Approved by (Position/Title) | text | 26 | No | empty |
| f_12 | Reply | textarea | 84x4 | No | empty |
| f_13 | Replied by (Name) | text | 28 | No | empty |
| f_14 | Replied by (Position/Title) | text | 22 | No | empty |
| f_15 | Replied by (Signature) | text | 26 | No | empty |
| f_16 | Date (footer) | text | 10 | No | Current date |
| f_17 | Time (footer) | text | 6 | No | Current time |

**Validation:** Subject (f_5) is required for save. For email send, defaults to "--none supplied--" if empty.
**Save mechanism:** Saves to `ics` table with `type = "ICS 213"`.
**Email subject:** "ICS 213 - {form name}"
**Audit logging:** `do_log()` called with `LOG_ICS_MESSAGE_SEND` on email send.

---

### 3.2 ICS-202 Incident Objectives

Gen 1 (`ics202.php`) and Gen 2 (`ics/ics202.php`).

**Gen 1 Fields:**

| # | Field | Type | Size | Default |
|---|-------|------|------|---------|
| 1-2 | Date From/To | text | 10 | Current date |
| 3-4 | Time From/To | text | 5 | Current time |
| 5 | Objectives | textarea | 100x20 | empty |
| 6 | Operational Period Command Emphasis | textarea | 100x20 | empty |
| 7 | General Situational Awareness | textarea | 100x20 | empty |
| 8-9 | Site Safety Plan Yes/No | checkbox | -- | unchecked |
| 10 | Safety Plan Location | text | 90 | empty |
| 11-19 | IAP checklist (ICS 203-208, Map/Chart, Weather) | checkbox | -- | unchecked |
| 20-27 | Other Attachments (4 pairs: checkbox + text) | checkbox+text | 80 | empty |
| 60 | Prepared by | text | 23 | Current user |
| 61 | Position/Title | text | 23 | empty |
| 62 | Signature | text | 23 | empty |
| 63 | IC Name | text | 30 | empty |
| 64 | IC Signature | text | 30 | empty |
| 65 | IAP Page | text | 10 | empty |
| 66 | Date/Time | text | 70 | empty |

**Gen 2** uses the same field structure but with Gen 2 infrastructure (DB save, archive, etc.). Incident Name (f_0) required for save.

---

### 3.3 ICS-205 Radio Communications Plan

Gen 1 (`ics205.php`) and Gen 2 (`ics/ics205.php`).

**Fields:**

| # | Field | Type | Size | Default |
|---|-------|------|------|---------|
| 1 | Incident Name | text | 18-20 | empty (required) |
| 2 | Date From | text | 10 | Current date |
| 3 | Time From | text | 5 | Current time |
| 4 | Date To | text | 10 | Current date |
| 5 | Time To | text | 5 | Current time |
| 6-59 | Radio channel grid (9 rows x 6 columns) | textarea (1 row) | 6-22 | empty |
| | Columns: System/Cache, Channel, Function, Frequency, Assignment, Remarks | | | |
| 60 | Prepared by | text | 23-48 | Current user |
| 61 | Date | text | 10-12 | Current date |
| 62 | Time | text | 5 | Current time |

**Validation:** Incident Name is required.
**Note:** Labeled "ICS 205-CG" (Coast Guard variant) in Gen 2.

---

### 3.4 ICS-205A Communications List

Gen 1 (`ics205a.php`) and Gen 2 (`ics/ics205a.php`).

**Fields:**

| # | Field | Type | Size | Default |
|---|-------|------|------|---------|
| 1 | Date From | text | 10 | Current date |
| 2 | Date To | text | 10 | Current date |
| 3 | Incident Name | text | 20-36 | empty (required) |
| 4 | Time From | text | 5 | Current time |
| 5 | Time To | text | 5 | Current time |
| 6-95 | Contact grid (30 rows x 3 columns) | text | 11-56 | empty |
| | Columns: Assigned Position, Name (Alphabetized), Method(s) of Contact | | | |
| 96 | Prepared by | text | 20-24 | Current user |
| 97 | Position/Title | text | 10-12 | empty |
| 98 | Signature | text | 10-12 | empty |
| 99 | IAP Page | text | 2-4 | "1" (Gen 1) |
| 100 | Date | text | 10 | Current date |
| 101 | Time | text | 5 | Current time |

---

### 3.5 ICS-206 Medical Plan

**Gen 2 only** (`ics/ics206.php`). No Gen 1 equivalent.

**Fields:**

| Key | Field | Type | Size |
|-----|-------|------|------|
| f_0-f_1 | Date From/To | text | 8 |
| f_2 | Incident Name | text | 24 (required) |
| f_3-f_4 | Time From/To | text | 8 |
| f_5 | Medical Aid Station Name | text | 15 |
| f_6 | Location | text | 26 |
| f_7 | Contact Number/Freq | text | 17 |
| f_8-f_9 | Paramedic Yes/No | checkbox | paired |
| f_10-f_19 | 2 additional Medical Aid Stations (same pattern) | text/checkbox | -- |
| f_20-f_24 | Transportation section (ambulance name, location, contact, paramedic) | text/checkbox | -- |
| ... | Additional transportation entries | text/checkbox | -- |
| f_40-f_44 | Hospitals (name, address, contact, burn center, helipad checkboxes) | text/checkbox | -- |

**Note:** This form uses paired checkboxes (clicking Yes unchecks No and vice versa).

---

### 3.6 ICS-213RR Resource Request

Gen 1 (`ics213rr.php`) and Gen 2 (`ics/ics213rr.php`).

**Fields (81 items in Gen 1):**

| Section | Fields | Description |
|---------|--------|-------------|
| Header | f_0: Incident Name (required), f_1: Date/Time, f_2: Resource Request No. | |
| Order (9 rows) | Qty, Kind, Type, Detailed Description, Requested Arrival, Estimated Arrival, Cost | 7 fields per row |
| Footer | f_66: Delivery Location, f_67: Substitutes/Sources | |
| Requester | f_68: Name/Position, f_69: Priority, f_70: Section Chief Approval | |
| Logistics | f_71: Order Number, f_72: Supplier Phone, f_73: Supplier/POC, f_74: Notes (textarea), f_75: Auth Rep Signature, f_76: Date/Time, f_77: Order placed by | |
| Finance | f_78: Reply/Comments, f_79: Signature, f_80: Date/Time | |

**Special layout:** Uses vertical text labels ("Requester", "Log", "Fin") in rotated cells.
**Date format:** Gen 1 uses `ics_date` setting (copies from `date_format` on first use).

---

### 3.7 ICS-214 Activity Log

Gen 1 (`ics214.php`) and Gen 2 (`ics/ics214.php`).

**Fields:**

| # | Field | Type | Size | Default |
|---|-------|------|------|---------|
| 1-2 | Date From/To | text | 10 | Current date |
| 3 | Incident Name | text | 24-40 | empty (required) |
| 4-5 | Time From/To | text | 5 | Current time |
| 6 | Name | text | 20-28 | empty |
| 7 | ICS Position | text | 20-30 | empty |
| 8 | Home Agency (and Unit) | text | 20-30 | empty |
| 9-32 | Resources Assigned (8 rows x 3 cols: Name, ICS Position, Home Agency) | text | 20-36 | empty |
| 33-80 | Activity Log (24 rows x 2 cols: Date/Time, Notable Activities) | text | 14-68 | empty |
| 81 | Prepared by | text | 20 | empty |
| 82 | Position/Title | text | 12 | empty |
| 83 | Signature | text | 12 | empty |
| 84 | Date | text | 10 | Current date |
| 85 | Time | text | 5 | Current time |

---

### 3.8 ICS-214a Individual Log

**Gen 2 only** (`ics/ics214a.php`). No Gen 1 equivalent.

**Fields:**

| Key | Field | Type | Size |
|-----|-------|------|------|
| f_1 | Incident Name | text | 22 (required) |
| f_2-f_3 | Date/Time From | text | 12/6 |
| f_4-f_5 | Date/Time To | text | 12/6 |
| f_6 | Name | text | 30 |
| f_7 | ICS Position | text | 30 |
| f_8 | Home Agency | text | 30 |
| f_9-f_56 | Activity Log (24 rows x 2 cols: Date/Time, Major Events) | text | 16/74 |
| f_57 | Prepared by | text | 20 |
| f_58 | Position/Title | text | 20 |

**Differs from ICS-214:** Individual log tracks one person's activities, no Resources Assigned section.

---

### 3.9 ICS-221 Demobilization Check-Out

**Gen 2 only** (`ics/ics221.php`). No Gen 1 equivalent.

**Fields:**

| Key | Field | Type | Size |
|-----|-------|------|------|
| f_0 | Incident Name | text | 26 (required) |
| f_1 | Incident Number | text | 24 |
| f_2-f_3 | Planned Release Date/Time | text | 12/5 |
| f_4 | Resource or Personnel Released | text | 30 |
| f_5 | Order Request Number | text | 28 |
| f_6 | Supply Unit checkbox | checkbox | -- |
| f_7-f_9 | Supply Unit: Remarks, Name, Signature | text | 16-20 |
| f_10 | Communications Unit checkbox | checkbox | -- |
| f_11-f_13 | Communications Unit: Remarks, Name, Signature | text | 16-20 |
| f_14 | Facilities Unit checkbox | checkbox | -- |
| f_15-f_17 | Facilities Unit: Remarks, Name, Signature | text | 16-20 |
| ... | Additional check-out sections (Ground Support, Security, other) | checkbox+text | -- |

**Special features:** Uses paired checkboxes for Yes/No selections. Multiple check-out sections for different organizational units.

---

## 4. Winlink XML Format

### 4.1 Legacy Implementation

**The legacy codebase has NO Winlink XML export.** There is no XML generation in any of the root-level or ics/ subdirectory files.

### 4.2 NewUI Implementation (Already Built)

The NewUI already has a Winlink export at `newui-dev/newui/api/winlink-export.php`. Currently supports ICS-213 only.

**API endpoint:** `GET /api/winlink-export.php?form=ics213&ticket_id=X`

**XML Structure (Winlink Express / RMS Express format):**

```xml
<?xml version="1.0" encoding="utf-8"?>
<RMS_Express_Form>
  <form_parameters>
    <xml_file_version>1.0</xml_file_version>
    <rms_express_version>TicketsCAD_v4</rms_express_version>
    <submission_datetime>2026-04-03 14:30</submission_datetime>
    <senders_callsign></senders_callsign>
    <grid_square></grid_square>
    <display_form>ICS213_Viewer.html</display_form>
  </form_parameters>
  <variables>
    <msgseqnum></msgseqnum>
    <incidentname>Wildfire - Oak Ridge</incidentname>
    <datetime>2026-04-03 09:15</datetime>
    <to></to>
    <toposition></toposition>
    <from>My Agency Name</from>
    <fromposition>dispatch</fromposition>
    <subject>Fire - Wildfire - Oak Ridge</subject>
    <message>Type: Fire
Location: 123 Main St, Springfield
Severity: High
Status: Active
Description: ...</message>
    <approved_by></approved_by>
    <approved_position></approved_position>
    <approved_datetime></approved_datetime>
    <reply></reply>
    <reply_by></reply_by>
    <reply_position></reply_position>
    <reply_datetime></reply_datetime>
  </variables>
</RMS_Express_Form>
```

**Key details:**
- Filename format: `RMS_Express_Form_ICS213_{ticket_id}.xml`
- Pre-populates from incident data (type, location, severity, status, description)
- `display_form` references `ICS213_Viewer.html` (Winlink Express built-in viewer)
- Uses `area_title` setting for organization name
- `xmlSafe()` wrapper for HTML entity encoding

---

## 5. Comparison: Gen 1 vs Gen 2

| Capability | Gen 1 (Root) | Gen 2 (ics/) |
|-----------|--------------|---------------|
| Database persistence | None | Yes (`ics` table, JSON payload) |
| Save/reload | No | Yes |
| Archive/de-archive | No | Yes |
| Delete | No | Yes (archived forms only) |
| Email | Yes (immediate) | Yes (with save-first option) |
| Contact picker | Built into form flow | Separate step after save |
| Access control | None | `can_edit()` function |
| Audit logging | No | Yes (`do_log()` on send) |
| Input placeholders | No | Yes |
| Dirty tracking | No | Yes (hidden field + onchange) |
| Incident linkage | No | Optional text field (no FK) |
| Session check | Minimal | Redirects if no session |
| SQL injection protection | String concatenation | Parameterized queries |
| XSS protection | Minimal | `sanitize_string()`, `e()` |
| Forms available | 6 | 9 (adds 206, 214a, 221) |

---

## 6. Recommendations for NewUI Implementation

### 6.1 Architecture

1. **Use a single API endpoint** (`api/ics-forms.php`) with form type as a parameter rather than separate files per form. The Gen 2 approach of identical scaffolding duplicated across 9 files is a maintenance burden.

2. **Store form data as proper JSON** in the database (not base64-encoded). Use a `TEXT` or `JSON` column type. The legacy varchar(10000) limit could truncate large forms.

3. **Link forms to incidents** with a proper `ticket_id` foreign key column, while still allowing standalone forms. The legacy approach of a text field for incident name is inadequate.

4. **Use a single shared ICS table** matching the legacy schema concept, but add:
   - `ticket_id` INT NULL (optional FK to ticket table)
   - `version` INT for optimistic concurrency
   - Change `payload` to TEXT or JSON type
   - Add `created_at` TIMESTAMP
   - Use InnoDB instead of MyISAM

### 6.2 Form Rendering

1. **Define form schemas as JSON/PHP arrays** rather than hardcoding HTML. Each form's fields, types, sizes, and validation rules should be declarative data that a single renderer can process.

2. **Support both edit and read-only modes** via the same renderer (Gen 2 already does this with the `func == "m2"` check that swaps inputs for spans).

3. **Use Bootstrap 5 form components** instead of raw HTML tables with inline styles. The legacy forms use table-based layout with hardcoded inch measurements.

4. **Implement proper print CSS** with `@media print` rules. The legacy has no print stylesheet.

### 6.3 Email

1. **Use the NewUI message broker** instead of PHP `mail()`. This enables sending via SMTP (PHPMailer), SMS, Slack, etc.

2. **Generate a PDF attachment** option in addition to HTML email body. The legacy only sends HTML inline.

3. **Use UTF-8 encoding** instead of ISO-8859-1.

### 6.4 Winlink XML Export

1. **Extend the existing `winlink-export.php`** to support all form types, not just ICS-213. Each form type has its own Winlink viewer HTML file and variable set.

2. **Winlink form names for `display_form`:**
   - ICS-213: `ICS213_Viewer.html`
   - ICS-213RR: `ICS213RR_Viewer.html`
   - Other forms: verify exact viewer names from Winlink Express documentation

3. **Allow export from saved forms** (load from `ics` table) as well as from incident data.

### 6.5 Migration Considerations

1. **The legacy `ics` table can be migrated directly** since the schema is simple. The base64-JSON payloads can be decoded and re-encoded as proper JSON.

2. **Form field keys changed between generations** (Gen 1 uses `f1`, `f2`; Gen 2 uses `f_0`, `f_1`). The NewUI should normalize to a consistent naming scheme.

3. **The Gen 2 forms are the authoritative versions.** Gen 1 files are superseded but still present for backward compatibility.

### 6.6 Priority Order for Implementation

1. **ICS-213 General Message** -- most commonly used, already has Winlink export
2. **ICS-214 Activity Log** -- heavily used during incidents
3. **ICS-213RR Resource Request** -- important for logistics
4. **ICS-205 Radio Communications Plan** -- critical for amateur radio users
5. **ICS-205A Communications List** -- companion to ICS-205
6. **ICS-202 Incident Objectives** -- operational planning
7. **ICS-206 Medical Plan** -- medical/EMS focus
8. **ICS-221 Demobilization Check-Out** -- end-of-incident
9. **ICS-214a Individual Log** -- variant of 214
