Tickets README

-- Short Intro
Tickets is the product of a collaboration to produce a well-featured dispatch management 
and tracking application for the public safety community, with special attention paid 
to the needs of volunteer-staffed agencies.  Unlike too many other dispatch applications, 
Tickets is a true web-based application. All dispatch, tracking and search functions 
are accessible via the Internet using common web-browser software, making use by 
distributed teams and others simple, efficient and inexpensive.  As such, it allows 
access by the public and career public safety teams; they already have the desktop 
software they will need.

It is our hope that Tickets will help to break the widespread use of rudimentary tools in 
dispatch management by volunteer teams, and make it possible for them and other low-budget 
organizations that have lacked the resources needed to acquire software to effectively 
manage their dispatch operations.

Tickets is open-source software released under the GNU General Public License (GPL). The 
use of the GPL guarantees, in perpetuity, a users right to (a) freely use the Tickets 
software without charge, (b) freely redistribute the software and (c) freely modify 
the software to meet their needs or the needs of others. Any software derived from 
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

Tickets is  written in PHP and Javascript, using a MySQL database engine and the powerful and 
flexible Google maps. Requirements include a conventional web server such as Apache or IIS.
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
+ CSS Is used extensively.


-- Requirements
+ A PHP capable webserver (either apache: http://www.apache.org or MS's IIS)
+ PHP 4.1 or higher
+ MySQL, 3.x probably works, but 3.23.*/4 is preferred
+ You will need a GMaps API key. Obtain at http://www.google.com/apis/maps/signup.html
+ Clients must accept cookies for login info and handle CSS
+ Users are running phpticket on a 486 66mhz/64mb on linux 2.2.x and it's fast


-- Installation
+ Unzip the file into your intended Tickets directory.
+ Create an empty MySQL database.  You'll need information re server parameters for the install 
  script, next step.
+ Point yr browser to whatever/install.php, and go there.
+ Fill in the install form, including the GMaps API key. (URL of source is provided on the form.)
+ The install script does a lot, including creating two login accounts - admin/admin and guest/guest.
  You'll be notified of success at the script's completion -- in a second or two.
+ One of the first things you may want to do is to set up yr own default map center.  (While the Show 
    Tickets map is automatically centered on existing tickets, the NEW TICKET form uses the default 
    setting.  (Set that center via the CONFIGURATION/SET DEFAULT MAP link. Note lots of other 
    configuration settings there.)
+ Important: Move/delete/change permissions on 'install.php' so it can't be run after a succcessful
  installation.
+ Important: Login as Admin/admin and change administrator password for security.
+ If you need to change server address, obtain your free GMaps API key for the new domain 
  and enter it via the Configurations/Edit Settings/gmaps_api_key form field.
+ Edit any settings to your liking, at the CONFIGURATION/Edit Settings link.


-- About
Tickets was written by Arnie Shore, (shoreas@Gmail.com) as an adaptation of the original 
written by Daniel Netz (netz at home dot se) using PHP, Apache and MySQL 3.x/4.x on 
linux. Bug reports and feature requests  are always welcome and preferably discussed 
in the forum (see link below). 

This software is licensed under the GNU GPL license (see COPYING) and may be used and
distributed in any way it may suit you as long as it's according to GPL.


-- Security
Security is an important issue, although we have reasonable precautions in its implementation, 
it will  remain short of the security feaures that highly sensitive data might require. The 
most common attack, SQL injections, is protected against, and the login process is being enhanced 
to resist man-in-the-middle attacks. Ultimately, each site must employ its own tools to
resist the most common attack in many instances, that of the michievous insider.  As always, 
secure passwords are fundamental.


-- Misc
Personal contact is shoreas [at] gmail [dot] com