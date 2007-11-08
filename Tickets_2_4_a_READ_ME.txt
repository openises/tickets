=========================================================================

Tickets 2.4 BETA Release
 README File

=========================================================================

6 November Update:
The file tickets_2_4_a_upgrade.zip file is for those who 
downloaded the earlier 2_4 version of Tickets. If you have 
not previously downloaded a version of Tickets, then you 
should download and install Tickets 2_4_a_Beta.

=========================================================================

Thank you for downloading and installing the Tickets 2.4 
BETA Release. 

This is BETA software and still in a testing stage. It is 
not considered 'Ready for prime time' yet, but with your 
help we can get it there!

Please read the following instructions carefully. There are
a few things that you need to do BEFORE Tickets can run 
successfully on your machine.

1.  Unzip the file into its own directory. (Any valid 
    directory name will do.)
    
    * WAMP users will probably use one under wamp/www, 
      (ex: wamp/www/tickets)

    * XAMPP users should use the htdocs directory 
      (ex: xampp/htdocs/tickets)

    * If using another web server, the Tickets folder should
      be in the web root directory, for convenience.

2.  Create an empty database, probably using PHPMyAdmin. 
    (Any valid db name will do.)

    * For WAMP/XAMPP users, PHPMyAdmin is one of the tools 
      the package provides.

    * Remember the database name and connection parameters, 
      including Host, MySQL User Name and Password you 
      supplied for the MySQL installation.

3.  To start the install process, use your browser to open 
    the page "install.php" in the directory where you copied
    Tickets.

    * You'll then need to enter some of the information re 
      the just-created database from step 2 above.

    * The GMaps API key is already set for you if your local
      domain is either 'localhost' or ' 127.0.0.1 ' .  
      Otherwise, link to the Google site for that key, using
      the provided URL  (The key is free; read their Terms 
      of Use.)

    * Later, when you're ready for remote access - including
      by yourself - you'll need a 'real' GMaps key, not one 
      the one the installer provided for localhost use.  
      Obtain it at the Google URL provided, using your 
      domain name.  Read their Terms of Use.

4.  Log in as administrator using the automatically 
    generated userid and password. (This is mandatory for 
    the following steps.)  Now, PLEASE read the HELP/Readme 
    file.

5.  After that, you'll probably want to set your own map 
    default center/zoom.

    * Navigate to 'CONFIGURATION/Edit Settings/Set Default 
      Map' to do that.

6.  Next, you may want to set up your response units, via 
    the 'UNITS', 'Add a Unit' buttons.

    * Tickets will poll APRS for position reports if you've 
      defined the unit as 'remote', provided a callsign, and
      set a non-zero APRS poll time. See next step below for
      how to set that up.

7.  Hit 'CONFIGURATION/Edit Settings for a collection of 
    settings that you may want to revise in order to tailor 
    its operations a bit to your own needs. (Like the APRS 
    poll time value, in minutes.)

8.  Please, remember that Tickets is in BETA testing. Be 
    kind to us.  ;-)

    * But let us know what needs to be changed/improved/ 
      etc.

    * Stay tuned for new versions - Tickets is young and 
      growing.

9.  Please - no, make that PLEASE! - review the security 
    recommendations reference to the install script and its 
    automatically-created accounts.

10. Remember the default logins

    * Guest Account
      User Name: guest
      Password:  guest

    
    * Administrator Account
      User Name: Admin
      Password:  admin 

+++++++++++++++++++++++++++++++++++++++++++++++++++++

A User's guide will be available at a later date     

+++++++++++++++++++++++++++++++++++++++++++++++++++++

If you need help in setting up a web server on a local 
machine, be sure to view the tutorials at the OpenISES Wiki.
Adobe Acrobat (pdf) versions of the tutorials can be 
downloaded from there as well.

http://openises.wiki.sourceforge.net/Web+Servers+on+the+Desktop      

+++++++++++++++++++++++++++++++++++++++++++++++++++++

Comments/questions are most welcome!

The Tickets Development Team
Arnie Shore - Lead Developer


Changes:
6 November Update:

  * If you have downloaded the earlier version of Tickets, the 
    file 'tickets_2_4_a_upgrade.zip'will upgrade your version
    of Tickets to 2_4_a. 

  * If you have not previously downloaded a version of 
    Tickets, then you should download and install 
    Tickets 2_4_a_Beta.

************************************************************

Installation instructions for tickets_2_4_a_upgrade.zip file

************************************************************

1.  This upgrade applies ONLY to an already-installed 
    Version 2.4.  Install it as follows.

2.  Close your browser. (There's no need to reboot your 
    system.)

3.  Unzip the tickets_2_4_a_upgrade.zip file, into whatever 
    directory you've used for Tickets.  
        
    These new files, 15 in all, will simply over-write their
    namesakes.
        
4.  That's it.  Gentlemen and ladies, Start your Browsers!

====

What this upgrade accomplishes for you:

1.  Tickets had been unable to accommodate multi-line 
    descriptions in both tickets and responder units.  You
    may or may not have encountered that, depending on what
    you keyboarded.

2.  Something called 'frame-jumping' is now prevented.  That
    is, you might accidentally or otherwise, have entered a 
    URL that resembled the following:    
                 .../tickets/add.php
    (add.php being one of the files/scripts that does the 
    'add' function.)  This would blow away all of the 
    navigation links/buttons in the upper frame, with truly
    inexplicable behavior.  Tickets now recovers from such 
    an attempt - without a complaint.

3.  A number of minor fixes which, in the aggregate, cleans
    up some data handling - particularly with dates and time
    values.

Guys, thanks for bearing with me on these 'challenges'.  
It's truly appreciated.      


The Tickets Development Team
Arnie Shore - Lead Developer
============================================================

