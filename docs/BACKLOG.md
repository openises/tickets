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

*Add new backlog items below using the same format.*
