<?php
error_reporting(E_ALL);    
/*
6/1/10 initial release
7/28/10 Added inclusion of startup.inc.php for checking of network status and setting of file name variables to support no-maps versions of scripts.
8/30/10 session_start()
@session_start();                    // 8/30/10
require_once('./incs/functions.inc.php');		//7/28/10
*/
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2 Final//EN">
<HTML>
<HEAD>
<TITLE></TITLE>
<META NAME="Author" CONTENT="">
<META NAME="Keywords" CONTENT="">
<META NAME="Description" CONTENT="">
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
<META HTTP-EQUIV="Expires" CONTENT="0">
<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Content-Script-Type"    CONTENT="application/x-javascript">

</HEAD>
<BODY  style='overflow: scroll;' onLoad = "location.href = '#bottom';">
<PRE>
Notes re Tickets CAD V2.7 Beta:

1.  The Call Board function provides a display of current incident-unit assignments. (In effect, a Situation Management tool.)  Clicks there will provide a convenient direct link to incident and unit information.  Cleared incidents are shown with a strike-through for 24 hours in order to communicate that information to later shifts.
2.  Incident types are supported, with the few you'll see able to be extended by admin-privileged users as needed.  See note 32. below.
3.  Given a new call/incident, the next screen shows its location against existing response units, plus their straight-line distances and driving directions -  via both text and map.  Driving directions from any unit may be called up, with the closest one shown as the initial default.
4.  In writing up a new call, the automatic lookup is fairly effective for residential land-line phones, names (with city/state information), and street address.  When found, these return map locations.  (AKA geo-coding.)
5.  Any number of units may be committed to a given incident, as well as vice-versa.  A closed incident remains visible for 24 hours with a strike-through - for ready identification.
6.  The Reports function provides for day/week/month/year summaries, with pie-chart displays by location, priority, and incident-type.
7.  The EM Card function allows you access to the default one provided by Bob Austin, or yr own if you have one.
8.  There's a light-weight chat function available, which some teams find useful for in-the-center chat.

To install:
9.  Create an empty MySQL database entry.  You'll need to know its name, the host name, your user name and password.
10. Unzip the tickets_2_7_beta zip into whatever directory from which you plan to run Tickets.
11. Navigate to, say, http://localhost/tickets/install.php -- or its equivalent on your machine.
12. There, you'll be challenged for the database information used in step 9. above.
13. At some point -- maybe this one -- you'll need a valid GMaps API key.  It's free.  Obtain from Google at 
        http://www.google.com/apis/maps/signup.html

After installing but prior to use:

14. Note that the automatically-generated 'admin' account - despite its name - has privileges level of 'super'.  Only such supers can create new user accounts of this level, which shd be rarely done.
15. For security, you should create your user accounts, with suitable access privileges: Configuration/Add User.  Remove the automatically-created admin/admin account as soon as you've tested your own account with 'super' privileges.  Similar for guest/guest.
16. Also for security, you should hide the install.php file.  Unintended access can cause havoc.
17. Set a number of settings, like aprs poll, call board, chat time, login banner, host: Configuration/Edit settings. (Mouseover item to see explanation.)  
18. Set default map center:  Configuration/Set Default Map
19. Incident types: Configuration/Incident types fire, traffic
20. Unit status values: Configuration/Unit status types (available, unavailable)
21. Set map center, etc:  Configuration/Set Default Map  (the lookup city entry can help.)
22. Set GMaps API key:  Configuration/Set GMaps API key if needed.
23. Set Incident types: Configuration/Incident types/Add New In_types entry (ex: Animal, Domestic Violence, Public Nuisance)  Set Group and Sort to control the order within the dropdown list.
24. Set Settings, incl Call Board, chat time Default City, map height and width: Configuration/Settings (Again, mouseover item to see explanation) 
25. Set Response Units: Units/Add a Unit.  Mobile units will require a call sign entry, and these will receive APRS polling.  (Calls must match exactly their aprsworld entry.)
26. Set Unit status values: Config/Unit status types.
27. Set email addresses: Config/Contacts.  You'll need these if you'll be emailing incident data.
28. To apply kml files, insert these into the tickets/kml_files subdirectory.
29. To use your own EMS card file, replace the PDF in the ticket/emd_cards subdirectory. (FYI, Bob Austin will be providing updates to the one currently included.)

Odds and ends:

30: Tickets DOES use popups for certain functions.  These will need to be unblocked.
31. On a Win32 platform, whether IIS or Apache, the mail function is problematic; it may or may not work correctly.
32. The map height and width are user-settable, via item 16 above.
33. In the 'incident types' and 'unit status' drop-down lists, you can set background colors by suitable css settings for the option 'group'.  See the ones already set in stylesheet.php .
34. We make extensive use of mouseover hints in a number of places, esp where we need to truncate the data shown.
35. Lots more corrections and improvements to the Units and Call Board  operations.
36. Some performance improvements made.
37. KML file usage is now a settable option. (0/1 for no/yes)
38. New privilege level 'super' added, with potentially dangerous database maintenance options restricted to that level.

Changes from Tickets V2.7 (the immediate predecessor version)

35. LOTS more corrections and improvements to the Units, Call Board, and report features.  Fixed units are now referred to as 'stations', and may have a call sign.
36. APRS functions notably cleaned up.  We now keep the most recent seven days' tracks.
37. Tracks improved, and  allows each mobile unit track to occupy its own window - as many as you have mobile units.
38. On tracked units, the as-of time obtained via APRS data is so identified.
39. The Configuration summary now shows who's logged on, or failed to log off.
40. The Units Edit form now shows the number of active dispatches, if any.  Such would make the unit non-removeable.
41. Reports changed to accommodate gracefully references to deleted units and incidents.
42. Login revised to allow reports to show station login data correctly.

Changes for Tickets V2.7 e
43. The Configuration menu now has a 'Test call sign' function which allows testing a given call sign for APRS validity.
44. Incident infowindows now provide a direct link to allow a unit to be dispatched.
45. If a given Unit is dispatched, its infowindow now identify the incident being dispatched to, with an active link, and also a link to the dispatch/routes page.  N.B. Units without location information cannot be dispatched.

Changes for Tickets V2.8
46. USNG coordinates are now shown for incidents and unit locations.
47. You now have your choice of display format for coordinates; choice is set in Configuration/Settings (no surprise here). Apply (0) for DDD.ddddd, (1) for DDD MMM SS.ss, (2) for DDD MM.mm .
48. Tracks handling has had a significant cleanup, and now includes a graph of altitudes.
49. The top frame now shows the revised value when a 'setting' is revised and affects the display.
50. Graphics in Incident Reports now display correctly.

Changes for Tickets V2.8 b
51. Tables module was corrected to address reported problems in POST variable handling.
52. Minor corrections to filename includes in the help module.
53. Corrections to ticket EDIT and to APRS functions; 

Changes for Tickets V2.9
54. APRS data handling significantly corrected and improved. Repeated zero-speed posreps are not retained.
55. Dispatch to a unit that has its 'contact via' setting an email address - including one for cellphone texting - generates mail to that address.
56. Cellphones/texting messages will be automatically 'chunked' to reflect the size limit per message.
57. Operator-level users may now perform all needed ticket, unit and dispatch data updates.
59. The database schema was expanded to accommodate expected growth in Tickets capabilities.  Teams and members - with attributes of titles, skills, courses taken, certificates gained, plus document management and photos - are now schema elements, although without any code in this version.
60. 'Notifies' was expanded to accommodate automatic notification via email/texting whenever ANY ticket is edited or written, with a filter for severity.

Changes for Tickets V 2.9 A
61. Primarily a bug correction release.  Lots of odds and ends, including the install process, several in the Call Board operation and in the Zoom mini-map display.

Changes for Tickets V 2.10 C
62. Unit types are now (at last!) variable. You may create any number of types, each unit associated with a type.
63. Phone no. lookup is more effective, using 'White Pages' API in place of Google's.  A default area-code setting is included.
64. The set of 500 icons has been eliminated in favor of dynamic number generation as required.  (Big reduction in zip file size.)
65. The Call Board may now be set to allow a permanent, frame-based display, as an alternative to the floating window, or none.
66. Its operation is a good bit quicker, providing dispatch status setting on-screen with no further navigation. Cleared dispatches may be hidden.
67. Whether a new Ticket goes immediately to the auto-routing function is user-settable.  You may turn that off/on.
68. Ticket serial number identification is now a settable option. The serial may be pre-pended, appended, or not - to the incident name.
69. Users may now write a log entry, of any content, as well as view log contents. Log-file housekeeping was added.
70. Unit icons are identified by letter, distinguishing these from incident icons.  A limit of 26 was raised to two alpha characters.
71. As a security measure, functions buttons are shown only upon login.
72. USNG values may be entered for incident and unit locations.
73. User information has been significantly expanded to accommodate future team management capabilities.
74. Corrections applied to unit status entries to prevent - as well as accommodate - invalid entries.
75. Reports module now has a report on dispatch performance, plus unit and incident selections.
76. A new user category, 'Member', is available; these are disallowed login, pending team admin functions being made available.
77. Google's Streetview is now available form the add page, on map point selection.
78. Some persistence functions added, notably situation page 'hide units' and also the Call Board's cleared dispatches.
79. Dispatched units may receive email or text msg notification;  message text may be edited prior to release.
80. Terrain map display is now a settings option.

Changes for Tickets V 2.10 D and E
81.  Instamapper interface has been added to support GPS position data from a number of handhelds, notably the blackberry.
        (Which means there no Tickets 'page' for the bb;  We take position data from the Instamapper server as required.)
82.  Support for this interface appears in unit information display.
83.  Units for which driving directions to an incident wd be inappropriate may now be identified, and directions will not be generated.
84.  Editing of generated email is now supported, for both email selected off the incident mini-menu and also upon dispatch.
85.  A 'quick' option setting has been added, which bypasses certain user notification in the interest of rapid operation.
86.  Settings are available to allow default email contents, applicable to noted email and also for 'notify' on new and edited incidents.
    (These are 'msg_text_1' for notify, 'msg_text_2' for incident mini-menu email, and 'msg_text_3' for dispatch notification.) 
    The defaults are set as character strings using the following.  (I cdn't find a more mnemonic scheme.)

            Subject        A
            Incident    B
            Priority    C
            Nature        D
            Written        E
            Updated        F
            Reporter    G
            Phone:         H
            Status:        I
            Address        J
            Description    K
            Disposition    L
            Start/end    M
            Map coords    N
            Actions        O
            Patients    P
            Host        Q
            911 contact R
            
    Thus a setting of 'C J D H' wd generate Priority, Address, Nature and Phone no, - in that order - in the to-be-edited message. 
    NB: An empty value is the default; it will generate all of the above, and in that order.
87.  A new settings entry - def zoom fixed - allows you to maintain a fixed map and zoom.  Value 0 applies dynamic zoom (original), 
    value 1 fixes only the situation screen, value 2 fixes only the units screen, and value 3 fixes both these screens.
88.  A new settings entry allows you to set the time interval - in hours - during which closed incidents are visible in the 
    Situation screen and cleared dispatches are visible on the Call Board.

Changes for Tickets V 2.10 F

89.  The situation display has been improved to provide more information.  A new popup feature will open a window on the selected 
     incident, with assigned units also shown. Mobile units are distinguished from fixed ones by icon color, black/white.
90.  You may now associate a 'protocol' with each incident type.  The protocol may be whatever category of information your operation 
     finds useful.  It may be the response protocol in, say, vehicle types and manning or specialties, or else the treatment protocol.   
91.  Mail functions have been significantly improved; 
     (a) mail to all/selected users - 'members' is now available on the Config screen.
     (b) Mail to all/selected response units is now available on the Units screen.
     (c) A new settings item, email from, is available.
     (d) SMTP mail connection is now supported, with a test function to assist in getting the connection parameters correct.  See 
         the new configuration/settings value of 'smtp acct'.  Once you have a satisfactory test operation, enter data in a 
         fwd-slash delimited string consisting of the smtp account, the port number, the user account (on that server), and the 
         password.  Your ISP may or may not require a fifth item, a 'from' string. (The following example does NOT show this latter item.)
             outgoing.verizon.net/123/ashore999/whatever
92.  For those of you implementing the Instamapper interface, the Configuration page does have a test function available.

Changes for Tickets V 2.10 G

93.  Google Latitude, LocateA and Gtrack have been added as tracking devices/services. Setting for Gtrack URL is on the configuration page. 
     Selection of the service is added by a convenient drop down list in the page where units are defined. The URL for the gtrack server is
     input on the configuration page. If no URL is input here, Gtrack will not be shown as an option.
94.  There is a new entry on the units page to allow use of "Handle" IAW user convention. This is in addition to the call sign/license key
     entry which must be the same as used by the chosen tracking service - APRS call sign will often be the same entry as Handle but for services
     such as LocateA, Gtrack, Instamapper and Google Latitude the setting in call sign will in general not be of any use when calling up the
     unit as per local convention and communication type.
95.  Test screens have been added which allow testing of the three additional tracking services, accessible from the configuration page. They
     require a valid tracking id to work and in the case of Gtrack, a valid URL. Basic details of how to implement a Gtrack system are included
     in the file "gtrack_readme.txt". The other services are hosted and therefore require little setup besides getting an account and a user ID.
96.  Setting for default Map Type (map type) allows you to set whether the default view for the Google Map element is standard Roadmap,
     Satellite Map, Terrain Map or Hybrid. Buttons still exist to allow changing from the default during use.
97.  Setting for locale added to configuration settings to allow for the options 0=US, 1=UK, 2=ROW. This determines whether US National Grid, 
     Ordnance Survey National Grid for the UK or UTM grid references are shown. This setting also changes date display on pulldown menu for Add and 
     Edit incident files.
98.  Hiding/showing units on situation display as well as by specific incident priorities is now provided.
99.  User-defined function buttons have now been added, with these appearing when set on the top menu screen. Setting of these function keys is 
     achieved through the configuration page. The entry follows the convention "URL, Function Key Text". There is a default setting for function 
     key one of the home page for the OpneISES project. The function keys will bring up a popup window and therefore popups are required (same 
     as for use of Incident popup, Tracks and the EM Card capability). Function keys that have no URL or text stored will not show therefore 
     no screen space is taken.  A possible use for this would be to server additional documents stored on the server such as local procedures 
     or training documents.
100. Per suggestion, and to add convenience in adding text to incident synopsis and disposition, we've added links to do so.  You'll see the 
     links on the incident map info-window and also the mini-menu.  Your entered text will be time-stamped.  The text volume is essentially 
     unlimited.
101. Incident type attributes has been expanded to allow adding a circle around the incident location.  You may specify circle radius (in miles, 
     color (using the common #rrggbb notation) and opacity (expressed as a number in the range 0-9, but start with 3 to see its effects). Try 
     using a group of these overlapped to identify an area that might be identified as 'do-not-enter', for emergency use.
     
The above may be too terse.  Let us know where further expansion wd be useful.

Changes for Tickets V 2.11 A and B

102. Added Facilities. Facilities are treated by Tickets in two ways, either to be routed to as the main incident location - possible use
     for distribution depots. Location of incident can be set by selecting the incident from a pull down menu - lat and lng are set from
     the facility details. Facilities also have additional information stored such as Security and access details. Facilities have two additional
     tables to set status and type in a similar manner to units. Facilities have their own icon types to distinguish from incidents and units.
     Facility types and status values are set from the configuration screen.
103. Added ability to set a facility as a receiving point for people from an incident - useful for EMS, Police or disaster scenarios where
     the facility could be a receiving station. Enhanced the unit status to now include en-route to facility and arrived at facility. Added
     Log events for future reporting against facilities.
104. Added Mail directions to a unit. An operator can either get route details for a facility and without dispatching a unit, email the route
     directions to any of the units or when dispatching a unit to an incident, as well as any other communications can email the directions.
     The directions capability has been enhanced to allow for multi point directions - i.e. current location to incident to receiving facility.
105. Facilities are hidden by default on the situation screen but the markers and sidebar can be displayed by clicking a show facilities link.
     Clicking on the sidebar or marker brings up an infowindow with details of the facility including opening hours, contact, security and
     access details.
106. There is now a "Links" button in the top menu which groups the function keys from 2_10_G under 1 button to save screen room. Clicking the
     "Links" button displays the function keys underneath the main menu buttons.
107. There is a Full Screen popup button in the main menu which opens a full screen map view of the situation map (excluding the sidebar).
108. There is now the ability to set a ticket with a status of "Scheduled". The Scheduled status is for pre-booked calls for some time in the future.
     If the status is set as Scheduled then the ticket will appear on a separate screen. An additional button now appears under the sidebar for
     Scheduled tickets in addition to the closed tickets button. Once a "Scheduled" ticket becomes due, the status can be changed to open and the
     ticket dealt with as normal. It will now appear in the main situation screen. Scheduled tickets retain the coloring by priority of other
     tickets.
109. The Unit and facility indices shown in the lists and on the markers now use a shortened version of the name. How this works is that when
     Entering a name into a unit or facility definition, follow the name with a "/" and three characters. The three characters will show up
     as the index. An example would be to define a new or edit an existing unit or facility and set the name as "Tickets User/T01". The T01
     will show up as the index in the list and on the marker and just the name "Tickets User" will appear as the name in both the lists and
     the infowindows. 
110. A frequent fliers facility has been added - this checks previous "reported by" entries in the database to check for persistent offenders.
111. In the Situation screen and full screen popup you can hide unavailable units. An field has been added to the unit status table to select
     which status types are identified as unavailable and can therefore be hidden.
112. The Scheduled and Closed Incidents buttons on the situation screen under the list of tickets and units are only shown if there are tickets
     in the database that have this status. You will not see the Scheduled Incidents button if there are no Scheduled incidents and in the same way
     the closed incidents button only appears if there are any closed incidents on the database. 
113. The call board has been upgraded to include the times for Facilities en-route and Facilities arrive events.  A List option has been added
     which shows elapsed time from incident start to each call event.  In addition, the report shows color-coded values for those times 
     exceeding certain threshold values, the latter set-able by users to meet local standards, and set by incident priority.. While displayed 
     values are shown in compact form, cursor 'mouse-over' displays the full value.

Changes for Tickets V 2.11 D

114. A significantly improved multi-user capability:  Buttons in the top panel are 'lit' when an event occurs that 
     merits user attention.  Specifically, a new incident written by another user will light up the situation button 
     in red; A unit movement will light up that button in blue; a chat invitation will light up the chat button in red.
     Lit buttons remain so until clicked, with a red situation button taking precedence over the blue color used to
     signify Unit movement.
     Given such notification, the screen refresh is no longer needed, and this function - which can be disruptive - has 
     been removed.    
115. The situation screen is more informative, and has improved handling for sites with a larger number of units. 
     The list is now fitted to the screen, with a scrollbar available to navigate the list.  In addition, email 
     to all or selected units is supported on the situation screen, thus saving a number of steps.  
     Mouseover assigned units displays target incident information, and Mouseover incidents displays incident 
     address data.    
116. The full-screen operation has a revised layout, with an expanded map portion and more intuitive yet reduced 
     overhead space.
117. This version now accommodates IPV6, the upcoming Internet addressing standard
118. The Chat function is much improved, supporting chat invites and with the above-noted button light-up as 
     notification of an invitation you've received. You may send invitations to all or to a selected user.   
119. The Configuration screen adds information re users, including identification of those currently logged-in.
120. We've revised the 'new Unit' form to help ensure that for tracked units - APRS, Instamapper, etc. - the 
     correct identification key is entered.

Changes for Tickets V 2.11 E

121. The 'excessive logins' nuisance has been corrected.
122. The Situation screen and the Units module have color-coding added for ready identification of unit type and 
     also status.
123. The Situation screen Units sidebar now sorts currently dispatched units to the top of the list.
124. Hiding Sit screen unavailable units now hides the sidebar list entries as well as their icons.
125. On the Sit screen, you may change unit status directly - no need to navigate to the Units module for that.
126. Units shown in the Routes module appear in order of their straight-line proximity to the incident.
127. Hide/Show of various system elements is now 'remembered' for the duration of your session, instead of 
     being lost when you navigate away.
128. A 'constituents' table has been added, in which you may load known user locations and associated 
     information, such as apartment number, key location, etc.  Phone no. lookup order is first past tickets, then
     constituent entries, and then white-pages.  (If you already have such a named table, you will need to 
     re-name it for this capability to work.)
129. A 'settings' value for reports pie chart diameter was added, allowing user-specified values when the defaults 
     require revision in order to meet operational needs.    
130. The Reports module has been expanded to include a new Incident Log' report, which show all of the activities
     associated with the selected incident.  In addition, the selection form was revised for ease of use.

Changes for Tickets V 2.11 F

131.  Chat works, along with more reliable multi-user operation, notification of new incidents, unit status change, 
      and unit movement via 'lit-up' buttons.
132.  Unavailable units sort to the bottom of the units dispatch list; these may be identified to allow or 
      disallow dispatch.
133.  Dispatching a unit no longer needs the 'multi' workaround.
134.  'Close_incident' operation has been corrected.
135.  Show/hide 'unit/facility unavailable' now persists for the session duration.
136.  A 'zoom_tight' setting pulls a close-in zoom for incident view/edit. 
137.  A time-of-day clock on the top frame.
138.  The situation screen reports the number of units assigned each incident.  Open (un-dispatched-to)
      incidents blink the count value.  Closed tickets my be selected by time frame (today, this week,
      month, etc.,) and full details are shown for these.
139.  This version includes the (minor) changes needed for operation with asterisk, the Open Source PBX. 
140.  The constituents table provides for apartment no and four phone nos .
141.  Automatic text messages (SMS) are now optimized for size by stripping data captions, possibly 
      reducing the number of tweets. 
142.  This file is now available via link on the Config screen. 

(Scroll up for additional information re earlier versions; note that much of the information remains pertinent.)

Changes for Tickets V 2.11 G

143.  An audible alarm capability has been added in order to augment the 'lit-button' signal that a new 
      incident has been written or a chat invitation is available.  The default sound files can be changed  
      via settings, and a test popup is available to help choose.  (Sound requires use of recent browsers; 
      IE is not yet supported for this.)
144.  We've added street location information plus geo-coding for both the Units and Facilities modules.
145.  Incident types now include a default priority selection.
146.  A '911 contact' information field has been added to Ticket information.  Letter code 'R' will include
      this field in mail messages.
147.  Certain SMTP  servers - notably Google's - require a security setting in the parameter strings, which 
      was not accommodated:  Now corrected.  (Users with existing smtp setting will need to adjust these.
      per instructions to be provided separately.)  In addition, the test function use is restricted to 
      super-admin's, as is settings edit.  Thanks due to Kurt Jack for his work on this.
148.  Call board operation has been improved with quicker navigation; refresh is limited to the affected frame.       

Changes for Tickets V 2.12

149.  A major addition: An internet option has been added, selected via a new setting: 'internet'.  Possible 
      values are 1, 2, 3, these representing respectively yes, no, and maybe.  While the others are 
      self-explanatory, in the 'maybe' setting we test Internet connectivity dynamically at each page load, 
      and operate accordingly WRT maps usage.
      Note that in addition to maps not being presented, services such as geo-coding, driving directions, 
      and white pages lookup are not supported.
      In the case of no connectivity, new incidents may be written, but sans geo-coding.  When 
      connectivity is restored these location are identified by a red question icon located at your map's
      default center; geo-location can then be made via the edit process.
150.  We've added the capability for each site to apply its own terminology and language to many menu items,
      button captions and field names.  See the 'Captions' link on the Config page, which takes you to the 
      selection and edit page. (The replacement text is identical to the base text initially.)  We've 
      included a Restore option for your convenience in testing.  (This capability is partially 
      incomplete; let us know of any gotta-have's.)
      We're especially interested in hearing from any of you who've implemented non-English values.
151.  Unit lists now highlight the dispatch status (D R O, etc.) of committed units, as well as the 
      count for units on multiple runs.  We've also added the ability for each site to apply its own 
      terminology for the noted dispatch status display values.  (See setting 'disp stat' for this.)
152.  We've added a 'unit' login capability oriented to use in mobile terminals or smart phones. Button 
      sizes and font are oriented to use with touch-screen devices.  Other user types also have access 
      to this new 'mobile' module, via a new top-frame button.  
      In creating a user of type 'unit', a dropdown list of existing units is presented, one of which 
      must be selected in order to be associated with this user.        
      In the case of multiple current calls, these are shown in the new page for possible selection.  A 
      unit user's identification is presented on the top frame's navigation buttons.
      At login-time, the 'unit' user will be taken to the new 'mobile' module, which provides buttons for 
      setting dispatch and unit status.  Capabilities inappropriate to this unit's operation are restricted.      
153.  Reverse geo-coding - i.e., click a map point to locate nearby addresses where feasible - has been 
      cleaned up to place available information into the page form for facilities and fixed-position
      response units.
154.  The 'quick' mode of operation has been improved by bypassing some previously required clicks.
155.  The logout button has been relocated for improved visibilty.
156.  In order to better accommodate a wide variety of screen widths and map sizes, we've revised the 
      important routes/dispatch module to provide for dragging the group of buttons to any convenient 
      screen position.  These buttons hold position during page scrolling.    
157.  We've added a setting value which will allow a site to implement its policy re whether an operator/
      dispatcher is allowed to revise incident details.  See setting 'Oper can edit'.

Changes for Tickets V 2.12 A

158.  Two new reports added;  'after-action'  and 'incident management'.  These are accessed via new 
      radio buttons as with other reports.
159.  The mobile module has been expanded with buttons better suited to touch screen operation as well 
      as functions related to operation by users with mobile terminals.
160.  Search capability has been enhance;  the generic table processor now has a Search button, and 
      the ticket search function is searching through actions and persions records correctly.  

Changes for Tickets V 2.12b

161.  The situation screen has been given a significant re-do, with much flexibility added to hide/show
      the several tables and classes of incidents, units, and facilities. A setting for "group_or_dispatch"
      allows the use of standard "Available" and "Unavailable" show and hide (determined by the hide setting
      in the unit_status table) or to use the unit status groupings as the hide and show categories. the 
      settings for standard are 0 (default) or 1 for status groupings.
162.  We've added the capability to maintain your own table of codes/signals, which may quickly be inserted 
      into several of the information fields used working an incident.  Use the 'Signals' link on the 
      Config page on this.  You'll have the ability in the New and Edit forms and others.
163.  If you prefer that all outgoing mail go as blind copies:  On Config/Edit settings, append /B to 
      the 'email from' setting;  e.g., 'yrname@gmail.com/B'.   In addition, mail operations now allow using 
      GMail smtp account.  N.B., that outgoing mail requires valid email addresses for both 'email from' 
      and 'email reply to'  settings.
164.  You may now dispatch units by capability. On the dispatch menu, entered terms are used to identify 
      units with matching capabilities for dispatching, with 'any' and 'all' options for term matching.
165.  We've added automatic incident numbering, a widely-requested capability and which supports a number 
      of styles, including a fixed string (useful in identifying events) and the current year.  
      Set it on Config/Incident Numbers.    
166.  There is an added entry in the configuration screen - Add Tickets Module. This is for future use.      

Changes for Tickets V 2.13  (Most of it by Andy Harvey)

167.  A new capability to use either "Day" - standard light version or "Night" - dark colors. This enables use
      in reduced light situations to avoid eye strain.  All colors can also be customised within the configuration
      module.
      PLEASE NOTE. Tickets now uses a different stylesheet than previously - default.css is only used for
      the installation stage. Once installed tickets uses "stylesheet.php" to handle the different color capabilities.
      This will mean however that any users that have customised their colors and styles should review the 
      differences between stylesheet.php and default.css. In stylesheet.php you will notice that some of the
      colors are set from a table entry. These are changed through the configuration screen (edit Day CSS colors and
      edit Night CSS colors.
168.  The situation screen has had another extensive makeover with the ability to hide each of the individual
      sidebars for Incidents, Responders, Facilities and the show/hide controls for markers and the sidebars.
169.  The full screen display has been reworked to incorporate the same kind of show/hide capability for
      Incidents, Responders and Facilities as the Situation Screen, as well as to incorporate the day/night
      capability.  It also displays undispatched incidents at screen top and current runs underneath the map, 
      making for a more management-oriented, or 'day-room' type of display. Hiding of these boxes is by 
      standard "x" close button, showing of them is by hovering over the tabs at the side of the screen.
      The showing and hiding of markers is also done in a box of this type (these controls are hidden as
      default) which is shown by hovering over the "Markers" tab at the side of the screen. Scheduled runs
      display is got to from the "Change Display" select menu in the bar at the bottom of the map.
170.  We've changed our data source for APRS information to aprs.fi, and we expect more reliable APRS 
      operation than in the past.  You WILL need to obtain a key from them - it's free: Go to www.aprs.fi 
      to obtain, and enter that value in 'Config/Edit settings/aprs fi key' field.  A test page is available at 
      Config/Test: APRS.
171.  We've added Google-style hints for the New incident form's city field.  Populate your own city list at
      Config/Places.
172.  We've added the capability to revise the operator mouseover hints on the New Incident form to meet 
      your standards and terminology.  Revise the default values at Config/Hints.
173.  We've expanded the Unit ID information on the sit screen and Units list to accommodate 6-character
      values.  The icon string remains at three character, and is taken from the low-order portion of unit
      name.
174.  While not inherent to this release, Tickets now has a User's Manual - thanks to Alan Jump;  consult 
      that for information on Tickets setup and usage - rather than these cursory notes.

Changes for Tickets V 2.13A

175.  Log information field sizes have been increased.  The Incident Management report now includes 
      information log entries.  Reports now have a 'full width' option, in which longer data elements
      are not truncated.
176.  Unit and facility handles are used in the Situation Screen as the primary identification.  (Where the 
      handle fields weren't used these were filled automatically from the unit name.   In addition, the 
      short icon string is now a separately editable field, also filled  automatically for you.  (We 
      recommend you review your installation on both these points.)
177.  The dark/light color scheme may now be switched at any time while logged in.
178.  We applied a significant cleanup to the remote tracking modules; operation should be more reliable.
179.  On the situation screen, the call dispatch status now shows the time in minutes (maximum 99) since
      last update.

Changes for Tickets V 2.13 B

180.  This is primarily a maintenance release, with fixes applied to geo-location processing in the several
      modules using that capability, to notifies (which now honor incident-severity filtering), and to 
      faciliities handling.  Some extra strengthening against unauthorized intrusion is included.
181.  You may now place the Tickets User Config and Operating Manual online via a link in the top frame. To
      do so, store that pdf file in the new 'manual' subdirectory.  Clicking on the "Manual" link will open 
      the first pdf file in that subdirectory, thus providing you with some flexibility, including the 
      ability to make your own revisions.

Changes for Tickets V 2.13 C

182.  'Person' data has been expanded to include additional elements, notably Insurance - with a select list and
	  supporting reviseable table, accessible via Config/Insurance.  New data elements have reviseable captions
	  in order to provide for terminology revision and translation.  The count of Person record is now highlighted 
	  on the Situation screen, and is now click-able to reach information details.
183.  Automatic page refresh has been implemented for the Situation screen and Full-screen displays; the time period
	  selected by the Setting entry 'Situ refr' in seconds.  A zero value indicates no automatic refresh, and a
	  15-second lower limit is imposed.  Please note: page refresh times will depend on network capability and the 
	  browser used, as well as the number of incidents, response units and facilities.
184.  Situation screen layout has been revised on both the standard as well as no-maps version.
185.  KML file handling has been enhanced to allow for external files, addressed via URL.  To do so, insert a 
	  text file (anything.txt) with any number of complete valid urls (each on a separate line) into the 
	  kml_files sub-directory.  This is in addition to any kml files in that directory
	  
Changes for Tickets V 2.20 A

186.  Addition of "Regions" capability. Tickets, Users, Responders and Facilities can now assigned to one or more
      Regions on the system to enable partitioning into multiple operational groups or regions.
187.  Addition of Map Markup such as visible boundaries and text banners. Boundaries can be Polygons or Circles.
	  Boundaries can be assigned to Responders as Exclusion zones or Ringfences and to Facilities as Catchment
	  areas. Boundaries can also be assigned to "Regions". Map Markup can be set to appear on selected screens
	  however Regional Boundaries always appear on the Situation screen. On the situation screen, map markup items
	  can be selectively shown or hidden in the same way as the various types of map markers.
188.  The Title string is now revisable - entering text in the "Title String" setting in configuration causes
	  the default title string to be replaced, removing this text returns the default title string.
189.  A new Statistics user and Statistics screen have been created. This creates a similar type of screen
	  to a Call Center wall board. A statistics user is taken direct to this screen. You can configure various
	  types of operational statistics for showing in up to 8 statistics boxes. Thresholds can be assigned against the
	  statistics types to change the color of the boxes from white to yellow, orange and red (in ascending seriousness).
190.  Various fixes to reports, the call board and also to the Situation, Responder and Facility screen format.	  

And, as always, we've applied a goodly number of corrections.  Please ensure that you're running the latest 
available release before reporting a problem.   

Changes for Tickets V 2.20 B

191.  Various fixes, no new functionality.

Changes for Tickets V 2.20 C

192.  Fixes to Units and Facilities in no maps mode fixing issue with removal of Regions assignment when editing.
193.  Removed ability to assign Circles and Banners to exclusion zones and Ring fences. Only Polygons work with
      this feature.
194.  Provided for more graceful response to session timeout and certain internal errors, which will now force a 
      login.  These are logged, and will appear in the Station Log Report.
      
Changes for Tickets V 2.20 D and E  - This is largely a maintenance release with important corrections applied in 
      several functions.

195.  We've applied a significant changes to the mobile function: Open tickets are now shown, scheduled 
      incidents are clearly shown with a small clock icon, and - widely requested - it will auto-refresh on
      a new ticket, a change in dispatch status, or an action or person item written.
196.  We've accommodated the Google maps api key change; the pre-existing 86-character length requirement
      has been removed, and the default values based on it are no longer supplied.
      
Changes for Tickets V 2.20 F

197.  Revision to Regions view control - now can be docked or undocked from the top bar.
198.  Various fixes to units and facilities causing incorrect region allocations.
199.  Tickets now provides for an outbound simple ICS-213 general message capability, widely used within the 
      USA response communities. This is somewhat exploratory; we want to gauge user judgements on what other  
	  NIMS forms might be useful and how these might be communicated.
200.  We've implemented auto-refresh for the main/situation screen. With this, when another user has taken 
      some action that affects the Situation Screen, that screen refreshes itself, with an information string blinking  
	  briefly at frame top center.
201.  Revised database indexing to speed up Situation Screen loading. Volume users will see significantly improved 
      performance..
202.  Along with the usual minor corrections, we have significant corrections applied to the Open GTS interface.
203.  Supporting rapid deployment, we've added a quick start routine which allows users to setup basic settings 
      and populate a number of responders, types, status settings etc after first install.
204.  Guest login text is now hidden if guest account does not exist.

Changes for Tickets V 2.30A

205.  Added Messaging - 2 way email and SMS messaging. Integration into operations, creation of new messages
	  screen and view of messages for individual Incidents and Responders under their respective view screens.
	  2 way SMS messaging done through a gateway provider using an API - provider initially provided is SMS
	  Responder. Messaging interface is as much like a normal email client as is operationally possible.
206.  Revision to regions view controls to tidy up display.
207.  Added a "Nearby" button that display nearby incidents when creating a new Incident.
208.  Added a "Service User Portal". Allows users outside of the operation to request jobs to be done. These
      requests are put in a queue and shown to logged in operational users with options to accept or decline.
	  Status of requests is given real time to logged in "Service Users". A new user type has been created for
	  this feature.
209.  Messages that are associated with a particular incident are output in the after action report.  
210.  As usual, various corrections applied in this release regarding functionality and security.   

Changes for Tickets V 2.30B & C

211.  Various fixes to Messaging

Changes for Tickets V 2.40A

212.  Revised mapping to Google Maps API V3.
213.  Various other fixes to messaging.

Changes for Tickets V 2.40B

214.  Creation of map markup now supported - revised from Google maps API V2 to V3.
215.  New "Hello All Stations" (HAS) feature. Allows broadcast message to all logged in users.  Optional, see setting 
	  "broadcast", and note that the default value is 0, for 'off'.  This feature is experimental; when active, it
	  requires an Internet connection.
216.  Addition of setting to allow user definition of how many hours before current time, booked calls are hidden from 
	  the current situation screen.  See setting "hide_booked", where the default is 48 hours.
217.  Addition of system/messaging setting to determine whether incoming email messages from pop3 server are deleted 
	  after download. Look under messaging settings (note you only see these settings if the system setting
	  "use_messaging" is not 0.
218.  We now provide for an 'ICS-213' button in the top menu; see setting 'ics_top', where a setting of '1' exercises
	  this option.  Default is '0', for 'off'.
219.  Database schema was revised to add indexing to certain of the larger tables.  Volume users will see some 
	  performance gains.  And as usual various other fixes.

Changes for Tickets 2.40C

220.  Various fixes. No new functionality.

Changes for Tickets 2.40D

221.  Added PHP native mail test script
222.  Various fixes including messaging, auto unit status and some map functions.

Changes for Tickets 2.41A

223.  Major revisions to Portal - revised main screen, added statistics, new request now in a popup, added ability to 
      specifiy multiple address requests, provided marker infowindows. Requests via the portal are now acknowledged by 
	  email back to the user - needs user email address set. Also status changes (acceptance, decline) are also notified.
224.  Added Mail Lists (mailgroups). These are added and edited via config and collect potential entries from users, units
      and contacts. Mail Lists are currently only used for system messages (notifies etc) rather than normal mail functionality.
225.  Added "Responder Mobile" page. This is accessed directly via ./rm off your tickets directory or automatically if system
      setting "use responder mobile" is set to 1. The screen is set up for unit rather than admin use and provided specific
	  unit functions such as inbuilt tracking, messaging, chat, status updates (on-scene etc).
226.  The "Responder mobile" page uses Open Streetmaps and supports the use of maps downloaded to the Tickets server. To download
      map tiles go to the main tickets config page and see the link to "download map tiles". (please note that this can take a 
	  long time depending on the area chosen.
227.  Addition of some new fields to Incident screen ("About address" and "to address") and to units and facilities screens ("About Status
      and for facilities email or email list). For Facilities the email and email list send a notify of a new job dispatched to or from
	  that facility.
228.  Added location warnings. You can add new locations to be warned about (previous incidents etc) and when adding a new incident
      Tickets will look at that table and present warnings about any locations within a user definable distance (see config / edit settings).
229.  Revision to chat to correctly update logged in users during a current chat - checked on a regualr basis.
230.  Revision to the "Cleanse Regions" routine to enhance the ability to resolve regions based issues and correct any tickets, units etc
      that have not been allocated to a region or where there are duplicate entries.
231.  Auto Dispatch Status - Tickets can be set to automatically update the unit status based on a change in the dispatch status - the
      specific status values are user definable in config.
232.  Per session selection of show or hide maps available at login.
233.  Files - the ability to store files (documents / pictures) securely against Tickets, Units, Facilities, general tickets use and specific
      portal users. For portal users this could be to store client / service user specific training or contractual documents which are then only
	  viewable to that user. Can also set global Tickets or portal documents which could be generic processes, contact lists etc. File types can
	  be documents or pictures, executable scripts are excluded by the file upload script. The directory is protected and barred for direct access.
234.  Various other fixes as normal including coping where a logged in user has no allocated regions.

Changes for Tickets 2.41B

235.  Various fixes, no new functionality

Changes for Tickets 2.41C

236.  4 Fixes to Callboard and notifies.

Changes for Tickets 2.41D

237.  4 Fixes to Units, top bar, messaging, notifies, new Incident and Responder Mobile page.

Changes for Tickets 2.41E

238.  Added Live tracking of mobile tracked units to situation screen.
239.  Fixes to Units, Routes, Portal, New Incident and Location Warnings.

Changes for Tickets 2.41F

240.  Fixed google maps api for use with SSL.

Changes for Tickets 2.41G

241.  Added change password capability to portal.
242.  Added Xastir tracking- requires Xastir server to be setup and creation of Xastir DFB (from within Xastir). Xastir DB must be
      on the same server as Tickets but not necessarily on the same DB. 
243.  Added sound to new message notification and modified new request notification. Adde flag for number of unread messages to message button.
244.  Revised handling of Instamapper to support new Instamapper API.
245.  Fixed the units show / hide on the situation screen for incorrectly handling persistence across pages.
246.  Various other fixes

Changes for 2.41H

247.  Additional ICS forms now available (now supports 205, 205a, 213 and 214.
248.  Fix to Whitepages Looup
249.  Fix to KML file display.
250.  New system setting to restrict unit level users from viewing other data on the system apart from the mobile
      or Responder Mobile pages. Default for this is switched off, if you want to restrict units set the system setting 
      "restrict Units" to 1.

Changes for 2.41J

251.  Fix to KML display, fixes issue with polygons showing filled even though they shouldn't be.
252.  A 'buildings' capability has been added to the existing 'places' operation.  Using it when callers often  
      identify location by building name - as in residential and campus complexes - facilitates rapid  incident
      location, saving precious seconds in writing new incidents.
253.  Revision to mail handling defaulting assigned units to be checked ready to send.
254.  As usual, various fixes.

Changes for 2.41K

255.  Fix to KML display for new incident and edit incident.
256.  Fixed email sending from portal when using smtp mail.
257.  Fixed issue with messages stored for responder who no longer exists.
258.  Revised link within notify email.

Changes for 3.00A (almost too many changes to list)

259.  Major re-write to use Open Streetmaps instead of Google Maps.
260.  Use of Open Streetmaps allows downloading offline map tiles for use when not connected to the internet.
261.  Revised most screens and included new sidebar for Messages, regions, Map controls and Files.
262.  Added Major Incident feature. This is still in it's infancy and is open to suggestions.
263.  Added ability to remove files in the file list for Superadmin.

Changes for 3.01A bug fix release

264.  Bug fix release, no major new functionality.

Changes for 3.02A - bug fix release

265.  Bug fix release, no major new functionality.

Let us know if the above is too terse and where further expansion wd be useful.

Changes for 3.03A - bug fix release plus enhancement to Major Incidents and addition of OS-Watch feature

266.  Addition of OS-Watch feature which provides operators the ability to monitor if responders are on-scene
      for an extended period which may indicate a problem.
267.  Enhancements to Major Incidents.
268.  Change to Incident Edit form so that new notes are added to the disposition box with timestamp and in an
      ordered manner.
269.  Various maintenance fixes.

Changes for 3.04A

270.  Various maintenance fixes including fixing no-maps mode.

Changes for 3.05A

271.  Fix to Notifications.
272.  Additional setting for on-scene watch to determine who receives alerts.

Changes for 3.06A - Performance improvement release

273.  Various changes to improve load times especially for large installations.
274.  New feature for "Add" Incident - now searches various tables with address elements for existing
      matching entries and provides autocomplete suggestions.
275.  Added two new geolocation providers in addition to OSM Nominatim. Now can use Google or Bing
      with setting to determine provider used. Also added place name search box to map (does not work
	  with Internet Explorer currently.)
276.  Enhancement to Major Incidents - addition of location for command posts with map marker. 
277.  Selected Map Layer persists while logged in. Default Map layer can be set in settings using the setting 
      default map layer.
278.  Various other maintenance fixes.

Changes for 3.07A - Maintenance release

279.  Various maintenance fixes.

Changes for 3.08A - Additional features and maintenance release

280.  Various maintenance fixes.
281.  Addition of Ordnance Survey maps as basemap for all main map screens) (UK only).
282.  Complete revision of the service user portal to reflect changes to maps as well as update
      list display and add map infowindows with buttons to open requests.
283.  Revisions to Mobile screen.
284.  Addition of Twitter as a message destination. See <A HREF='twitter.txt'>Here</A> for more details.
285.  Revision to sidebar to use pictorial buttons as being more space efficient.

Changes for 3.09A - Maintenance release

286.  Fixes to mobile screen.
287.  Fixes to Map download and default map setting.
288.  Revision to OS Watch.
289.  Fix to Phone number lookup in new incident screen.
290.  Fix to Full screen.
291.  Fix to situation screen incident period selection.
292.  Revisions to access level permissions.
293.  Fix to Callboard and removal of callboard from mobile screen.
294.  Revisions to location lookup scripts to increase accuracy.
295.  Fixed Route to facility function. Ability to move responder to that facility.

Changes for 3.10A - Additional features and maintenance release

296.  Tickets now supports and is well tested with PHP7
297.  Complete re-write of HAS functionality. Tickets now has an onboard Websocket server (requires open ports)
      however can also still use an external websocket server - address details and port are configurable in
	  settings.
298.  Incorporated code from patch provide by user Matthew McKernan to add messaging to Motorola MotoTRBO digital
      Radios, tracking by FollowMee, SMS sending using SMS Broadcast - an Australian SMS broadcast company and 
	  various fixes to other functions. Thanks to Matty for this stirling piece of work.
299.  Added Facility Board - configure a new user as a "facility" which is a new setting and a user logged in with
      this user will see a board displaying all jobs either originating or dispatched to a facility once an assignment
	  has been made. Notes can be added, patient names can be hidden in settings and a user of the board can "Arrive" or
	  "Clear" a responder as they arrive / leave.
300.  Addition of new setting to "Customise Situation Screen" where you can switch on or off the Recent Activity and
      Statistics information. Recent activity is also minimised by default.
301.  Revised top menu bar to correctly limit display of links to disallow access for guest users to specific areas of
      Tickets.
302.  Added contect (Right click) menu to incident list to give quick access to frequently used functions (add note, 
      add action, add patient and print ticket). 
303.  Added new print Ticket screen accessed from context menu.
304.  Fix of incorrect display of responder marker on invalid position data.
305.  Fix for APRS tracking failing on some installations.
306.  Lots of Fixes and screen tidy ups.

Changes for 3.11A - Maintenance release

307.  Revisions to cater for Google requiring Maps API key again.
308.  Revisions to Messaging including ability to individually select messages for deletion, read status or restore.
309.  Revisions to standard messages and replacement text for standard messages, more options added and name field for
      the standard messages where the name rather than the message will show in the select menu.
310.  Lots of Fixes and screen tidy ups.

Changes for 3.12A - Maintenance release

311.  Reworked script for downloading maps to improve stability.
312.  Rework to geocoding to improve stability and accuracy.
313.  Other minor fixes.

Changes for 3.20 - Major re-write

314.  Screen look and feel, buttons and responsiveness to different screen sizes.
315.  Reworking of No Maps mode so that it can be used to simplify the screen but not limiting ability to geocode 
      addresses if you actually have a good internet connection. Means that dispatch in no maps mode will still have 
	  correct distances.
316.  Reports totally reworked with a couple of new reports thrown in for good measure. Also ability to download reports
      to Word or Excel format.
317.  Reworked standard messages to allow grouping of the messages in message groups, allow selective hiding of messages
      that are for a particular SMS Gateway provider, handling of multiple replacement text tags in a single standard 
	  message.
318.  New button on the situation screen, press this to show all assigned units on the map with a small red 
      infowindow – click on three dots in the infowindow brings up that responder detail infowindow.
319.  Some more work on the Mobile screen to improve the refresh of the elements in the background on a timed 
      basis with refresh only happening if there are changes.
320.  Additions to Major Incidents. Sorted some problems with it and also added the flagging of major incidents 
      at the top of the Situation, units and facilities screens if there are any Major Incidents active.
321.  Fixed some problems with portal operation, revised the Requests screen (now lists requests sorted by status.
322.  Some fixes and minor changes to messaging screens.
323.  Added a script to show PHP config information from the config screen
324.  Warn locations now show on the Maps using a triangle icon – click on it to see infowindow with warning details.
325.  Some reworking of search both to look and feel as well as to fix some problems.
326.  Addition of TRACCAR and javAPRSSrvr fpr APRS tracking.
327.  Performance enhancement to APRS.fi tracking.
328.  Added ability to set timezone from config, system now defaults to “America/New York” for systems where PHP ini settings 
      for timezone are not correctly implemented. Timezone is set from config using select control offering all options so user
      knowledge of what timezones exist is not necessary. Access to this is through “config / set default map”
329.  Added system setting for responder and facility default list sort (setting choses which of the columns to sort by for
      Both the situation screen and the responders / facilities screens. Also implemented session persistence for sorting if
      Lists sorted by clicking on column heading.
330.  Added “Txtlocal” as an SMS broadcast provider.
331.  Added setting to allow default SMS for message sending.
	 
<A NAME = 'bottom'></A>

July '17
Arnie Shore
Andy Harvey, UK

</PRE>
</BODY>
</HTML>
