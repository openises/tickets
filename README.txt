Tickets README

-- Short Intro
Tickets is the product of a collaboration to produce a well-featured dispatch management 
and tracking application for the public safety community, with special attention paid 
to the needs of volunteer-staffed agencies.  Unlike too many other dispatch applications, 
Tickets is a true web-based application. All dispatch, tracking and search functions 
are accessible via the Internet using common web-browser software, making use by 
distributed teams and other online access to dispatch information simple, efficient and 
inexpensive.  As such, it allows access by the public and career public safety teams.

It is our hope that Tickets will help to break the widespread use of rudimentary tools in 
dispatch management, and make it possible for low-budget organizations that have lacked 
the resources needed to acquire software to effectively manage their dispatch operations..

Tickets is intended as an alternative to low-cost but limited solutions such as 
spreadsheets and text documents that volunteer organizations have used to track dispatch 
operation. Tickets is open-source software released under the GNU General Public 
License (GPL). The use of the GPL guarantees, in perpetuity, a users right to (a) freely 
use the Tickets software without charge, (b) freely redistribute the software and (c) freely 
modify the software to meet their needs or the needs of others. Any software derived from 
Tickets is similarly bound by the GPL, ensuring that the software cannot be used as the 
basis of a proprietary product. 

Tickets is non-denominational.  It's a general-purpose solution to the problem of 
tracking response teams without specializing in, say, police, fire, 9-1-1 or emergency 
medicine, although the clean internal design allows ready expansion to meet more spec-
ialized requirements.  Contact the author regarding such specialized needs.

It is being made available to the public safety community in the same sense of 'giving back' 
volunteerism that informs so many public safety organizations.  It is designed to be useable
by the part-time or occasional user who will no real access to a training budget or schedule.

For more information contact us via the email link below.

Tickets is  written in PHP and using a MySQL database engine, and uses the powerful and 
flexible Google maps. Requirements include a convetional web server such as Apache or IIS.
Installation via the built-in install process, and is straightforward.

The additions to the basic package include mapping and adaptations suitable to a dispatch 
operation such as patient and responder tracking.  Capabilities include a address-to-map 
lookup functions, and very flexible and powerful mapping features without the costs and
cumbersome operation common to too many GIS-based tools.
The user interface is fairly simple and easy to use, 

Background: Tickets is a major upgrade to a mature and well-respected ticket-tracking 
application, PHP-Ticket.  The original author, having frequented the OpenTicket system used 
at KTHNOC (Network Operations Center at Royal Institute of Technology in Stockholm, Sweden) 
which is the node at which SUNET, NORDUNET and national ISPs connect (PoP),  felt a need to
list  tasks and troubles at work (being a sysadmin) to keep track of what needs to be done 
and to give other people a chance to read and post tickets. KTHNOC OpenTicket available at 
http://www.noc.kth.se/opentickets/index.html


-- Features
+ Mapping functions based on Google maps, which include zoom, pan, and satellite views.
+ Address lookup which shows the map location of most lookups for street address or town.
+ Automatic map centering based on the calculated center of current tickets.
+ Selective map view by incident severity - i.e., layers in GIS-speak
+ Automated installation through install.php
+ User management and login functions using sessions
+ Most configuration values are accessed via the interface; no database knowledge is needed.
+ Ability to send notification upon ticket changes by email
+ Printable tickets
+ Search capabilities - with results highlighted
+ All HTML is using CSS


-- Requirements
+ A PHP capable webserver (either apache: http://www.apache.org or MS's IIS, although the latter is untested at this writing)
+ PHP 4.1 or higher
+ MySQL, 3.x probably works, but 3.23.*/4 is preferred
+ You will need a GMaps API key. Obtain at http://www.google.com/apis/maps/signup.html
+ Clients must accept cookies for login info and handle CSS
+ Users are running phpticket on a 486 66mhz/64mb on linux 2.2.x and it's fast, we're told.


-- Installation
+ Move all files to a directory readable by the webserver.
+ Make sure the webserver user has write permissions on 'mysql.inc.php',
  ("chmod mysql.inc.php 666" probably does it on unix-flavored systems, or its equivalent 
  under MS-based servers) or the installation will fail. 
+ Point your browser to 'install.php' and follow the instructions.
+ Move/delete/change permissions on 'install.php' so it can't be run after installation.
+ Change permissions on 'mysql.inc.php' to 644 ("chmod mysql.inc.php 644").
+ If for any reason you need to change the GMaps API key, obtain your free GMaps API key and  
   enter it via the'Configuration/Edit Settings/gmaps_api_key' form field.
+ Login as Admin/admin and **** CHANGE ADMINISTRATOR PASSWORD **** for security reasons.
+ Edit any settings to your liking via the 'Configuration/Edit Settings' link.  Note especially the ability to set your own default map 
    center and zoom, via the 'Set Default Map' link.


-- About
Tickets was written by Arnie Shore, (shoreas@Gmail.com) as an adaptation of the original 
written by Daniel Netz (netz at home dot se) using PHP, Apache and MySQL 3.x/4.x on 
linux. Bug reports and feature requests  are always welcome and preferably discussed 
in the forum (see link below). 

This software is licensed under the GNU GPL license (see COPYING) and may be used and
distributed in any way it may suit you as long as it's according to GPL.
We've kept the interface simple and subtle for a reason, speed and ease of use. 

-- IMAP support
IMAP support is currently disabled.  Warning: when enabled, note that it is EXPERIMENTAL 
and must be used with precaution.  Please do NOT use this on a production 
database/server without proper testing. The IMAP support enables users to import tickets 
from external email accounts, protocols supported are IMAP4, POP3, NNTP, optionally over 
SSL. To enable IMAP support, set imap_support to 1 and set the other imap_* settings to 
an appropriate value. The imap_type setting defines the protocol used, these are the 
supported values:

IMAP4:		1
POP3:		2
IMAP4_SSL:	3
POP3_SSL:	4

When imap_support is enabled, administrators can use the "Import IMAP" link in
the configuration screen to import IMAP emails into the database. This behavior
is subject of change in later versions.


-- Security
As always, security is an important issue in Tickets. Second only to careless password
handling, a common attack is via SQL Injections - strings directly inserted into SQL queries that
can be devised to do any mischief possible with SQL.  While this is protected against with standard techniques.
password protection remains the first level of defense, as well as physical protection of the server area.


-- Misc
Personal contact is shoreas [at] gmail [dot] com