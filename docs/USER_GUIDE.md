# TicketsCAD User Guide

Version 3.44.0

This guide covers day-to-day operations for dispatchers, responders, and administrators using the TicketsCAD system.

---

## Table of Contents

1. [Getting Started](#getting-started)
2. [Navigation](#navigation)
3. [Situation Screen (Main)](#situation-screen)
4. [Creating a New Incident](#creating-a-new-incident)
5. [Editing an Incident](#editing-an-incident)
6. [Units / Responders](#units--responders)
7. [Dispatch / Routes](#dispatch--routes)
8. [Facilities](#facilities)
9. [Patient Records](#patient-records)
10. [Search](#search)
11. [Reports](#reports)
12. [Mobile Interface](#mobile-interface)
13. [Chat](#chat)
14. [Messaging](#messaging)
15. [Call Board](#call-board)
16. [Full Screen Map](#full-screen-map)
17. [Personnel](#personnel)
18. [SOPs (Standard Operating Procedures)](#sops)
19. [ICS Forms](#ics-forms)
20. [Service Requests Portal](#service-requests-portal)
21. [Day/Night Mode](#daynight-mode)

---

## Getting Started

### Logging In

Navigate to the system URL in your browser. The login screen appears in the lower frame. Enter your username and password, then click **Login**.

**User Roles:**
- **Admin / Super-Admin** — Full access to all modules including Configuration
- **Member** — Standard dispatch operations, no admin access
- **Guest** — Read-only access to Situation, Search, Help, and Full Screen Map
- **Unit** — Restricted view for field responder units

After login, the top navigation bar activates and the Situation screen loads.

### Session Timeout

Sessions expire after a period of inactivity (configured by the administrator). When expired, you will be prompted to log in again. Your work in progress is not saved on timeout.

---

## Navigation

The top toolbar provides access to all modules. Buttons shown depend on your user role:

| Button | Module | Access Level |
|--------|--------|-------------|
| Situation | Main dispatch screen with map and incident list | All |
| New | Create a new incident/ticket | Member+ |
| Units | View and manage response units | All |
| Fac's | View and manage facilities | All |
| Search | Search historical and active incidents | All |
| Reports | Generate activity reports | Member+ |
| Config | System administration | Admin only |
| SOP's | Standard Operating Procedures documents | Member+ |
| Chat | Real-time inter-team chat | Member+ |
| Help | System help documentation | All |
| Log | Activity audit log | Member+ |
| Full scr | Full-screen map display | All |
| Personnel | Personnel management | Member+ |
| Links | External reference links | Member+ |
| Board | Call board display | Member+ |
| Mobile | Mobile/field unit interface | Member+ |

The currently active module is highlighted in the toolbar. The status bar shows the logged-in user, permission level, active module name, and current time.

---

## Situation Screen

The main dispatch screen shows:

- **Map** — Geographic view of active incidents and unit positions
- **Incident List** — Active tickets sorted by time, with severity color coding
- **Unit Sidebar** — Response units with status indicators

### Severity Color Coding
Incidents are color-coded by severity level to provide at-a-glance prioritization.

### Incident Actions
Click on any incident row to:
- View full incident details
- Open a map popup showing the incident location
- Access the dispatch/routing screen
- Edit the incident
- View assigned units, actions, and messages

---

## Creating a New Incident

Click **New** in the toolbar to open the incident creation form.

### Required Fields
- **Scope/Title** — Brief description of the incident
- **Location** — Street address, city, state
- **Incident Type** — Select from configured categories
- **Severity** — Priority level

### Optional Fields
- Contact name and phone
- Description and comments
- Facility assignment
- GPS coordinates (auto-populated from address lookup if available)
- Protocol reference
- 911 call indicator

### After Submission
The new incident appears on the Situation screen. Notifications are sent to configured users. The system logs the creation with timestamp and creating user.

---

## Editing an Incident

Click on an incident in the Situation list, then select **Edit** from the popup menu.

### Editable Fields
All fields from creation are editable, plus:
- **Status** — Open, Closed, or other configured statuses
- **Problem Start/End Times** — Automatically managed based on status changes
- **Owner** — Assigned incident commander or responsible party
- **Receiving Facility** — Destination facility (e.g., hospital)
- **Booked Date** — Scheduled date for planned incidents

### Status Changes
- Setting status to **Closed** records the problem end time
- Re-opening a closed incident clears the end time

---

## Units / Responders

The Units screen displays all configured response units with:
- Current status (available, dispatched, out of service, etc.)
- GPS position and mobility indicator (stopped, moving, fast)
- Assigned incidents
- Capabilities and equipment

### Unit Status Colors
Units are color-coded by dispatch status to show availability at a glance.

### Updating Unit Status
Click on a unit to open its detail panel. Administrators can edit unit properties, change status, update position, and manage capabilities.

---

## Dispatch / Routes

Access from an incident's popup menu via **Dispatch** or **Routes**.

### Dispatching Units
1. The left panel shows available units sorted by distance from the incident
2. Check the box next to a unit to select it for dispatch
3. Click the dispatch action button to assign

### Route Display
The map shows the calculated route between the unit and the incident location.

### Unit Selection Rules
- Units already assigned to this incident show a checked, disabled checkbox
- Units on another active call show a disabled checkbox (unless multi-dispatch is enabled)
- Units marked as "no dispatch" are disabled
- Available units show an interactive checkbox

### Filtering
Use the **Filter by Capabilities** section at the bottom to filter units by equipment type or skill.

---

## Patient Records

Access from an incident's detail view. Used primarily in EMS/medical incidents.

### Adding a Patient Record

| Field | Required | Description |
|-------|----------|-------------|
| **Patient ID** | Yes* | Unique identifier for the patient (user-entered, max 32 characters). This is a tracking ID, not necessarily a government ID. Used to distinguish multiple patients on the same incident. |
| **Full Name** | No | Patient's full name |
| **Date of Birth** | No | Patient's date of birth |
| **Gender** | Conditional* | M, F, T, or U — required depending on system locale setting |
| **Insurance** | Conditional* | Insurance provider selection — required depending on system locale setting |
| **Facility** | No | Originating facility |
| **Facility Contact** | No | Contact person at the facility |
| **Description** | No | Medical notes, condition, treatment details |
| **Signal** | No | Patient condition/status signal code |
| **As of** | Auto | Timestamp of the record (defaults to current time) |

*Required fields are marked with a red asterisk (**\***) on the form.

### Known Issue — Required Field Indicators
The red asterisk markers on the form may not perfectly match the actual validation:
- **Patient ID** is always required and validated
- **Gender** and **Insurance** requirements depend on the system's `locale` setting — the asterisk may show even when the field is not enforced, or vice versa
- Insurance validation was made optional as of v3.40 but the asterisk indicator was not updated in all form variants

### Multiple Patients
For multi-casualty incidents, multiple patient records can be added to a single ticket. Each patient record is tracked independently with its own timeline.

---

## Search

Click **Search** in the toolbar to query incidents.

### Search Options
- **Search text** — Free text search across selected field
- **Search in** — Select which field to search (contact, street, city, description, etc.)
- **Year filter** — Limit results to a specific year
- **Status filter** — Open, Closed, or All
- **Sort by** — Date, problem start/end, severity, scope, owner
- **Sort direction** — Ascending or Descending

### Results
Results are displayed in a paginated table with search terms highlighted. Click any result to view the full incident detail.

---

## Reports

Click **Reports** in the toolbar to generate system reports.

### Available Report Types
Reports are configured by the administrator. Common reports include:
- Incident activity by date range
- Unit response statistics
- Incident type distribution
- Response time analysis

### Generating a Report
1. Select the report type
2. Set date range and any filters
3. Click **Run** to generate
4. Results can be viewed on-screen or printed

---

## Mobile Interface

Click **Mobile** in the toolbar to access the mobile-optimized dispatch view.

### Features
- Simplified incident list
- Map popup for each incident
- Touch-friendly interface
- Dispatch and status update capabilities

### Map Display
The mobile map view opens in a popup window. If the map appears blank or blurry on first load, wait a moment for tiles to render or refresh the page.

---

## Chat

Real-time messaging between logged-in users.

### Features
- Multi-user chat rooms
- Chat invitations
- Persistent message history
- Audible notification on new messages

---

## Messaging

Persistent messaging system for formal communications.

### Features
- Send messages to individual users or groups
- Message archive and history
- Notification alerts for unread messages

---

## Call Board

A visual display board showing active incidents with color-coded severity and elapsed time indicators. Useful for wall-mounted displays in dispatch centers.

---

## Full Screen Map

Maximized map view showing all active incidents and unit positions. Useful for situational awareness displays.

---

## Personnel

Manage personnel records including:
- Contact information
- Certifications and skills
- Assignment history
- Equipment inventory

---

## SOPs

Access Standard Operating Procedure documents. SOPs are PDF files maintained by administrators and accessible from the toolbar.

---

## ICS Forms

Generate and manage NIMS-compliant ICS-213 incident command system forms for formal incident documentation.

---

## Service Requests Portal

External-facing portal allowing the public to submit service requests. These requests can be linked to incidents by dispatchers.

---

## Day/Night Mode

Toggle between Day and Night display modes using the buttons in the toolbar status bar. Night mode uses darker colors to reduce eye strain during nighttime operations.

---

## Keyboard and Interface Tips

- The system uses a frameset layout — the top frame contains navigation, the main frame contains content
- Popup windows (map, chat, etc.) open in separate browser windows
- Use F5 to refresh the current frame if display issues occur
- Browser zoom affects all frames — use the system's font size settings in Configuration instead

---

*This document is maintained as part of the TicketsCAD modernization project.*
