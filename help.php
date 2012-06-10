<?php
/*
9/18/08 filename changes to XXX.txt
1/21/09 added show butts - re button menu
7/28/10 Added inclusion of startup.inc.php for checking of network status and setting of file name variables to support no-maps versions of scripts.
12/1/10 get_text Patient, disposition added
5/4/11 get_new_colors() added
*/

session_start();
require_once('./incs/functions.inc.php');
$patient = get_text("Patient");						// 12/1/10
$disposition = get_text("Disposition");				// 12/1/10

?>
<HTML>
<HEAD>
<LINK REL=StyleSheet HREF="default.css" TYPE="text/css">
<SCRIPT>
function ck_frames() {		//  onLoad = "ck_frames()"
	if(self.location.href==parent.location.href) {
		self.location.href = 'index.php';
		}
	else {
		parent.upper.show_butts();										// 1/21/09
		}
	}		// end function ck_frames()

	try {
		parent.frames["upper"].document.getElementById("whom").innerHTML  = "<?php print $_SESSION['user'];?>";
		parent.frames["upper"].document.getElementById("level").innerHTML = "<?php print get_level_text($_SESSION['level']);?>";
		parent.frames["upper"].document.getElementById("script").innerHTML  = "<?php print LessExtension(basename( __FILE__));?>";
		}
	catch(e) {
		}

	function get_new_colors() {								// 5/4/11
		window.location.href = '<?php print basename(__FILE__);?>';
		}


</SCRIPT>

</HEAD><BODY onLoad = "ck_frames()">
<FONT CLASS="header">Tickets Help</FONT><BR /><BR />
<LI> <A HREF="help.php?q=tickets">Background</A>
<LI> <A HREF="help.php?q=tickets"><?php print $patient; ?>, Actions, and <?php print $patient; ?> Data</A>
<LI> <A HREF="help.php?q=config">Configuration</A>
<LI> <A HREF="help.php?q=notify">Notifies</A>
<LI> <A HREF="help.php?q=develop">Developer Notes</A>
<LI> <A HREF="help.php?q=changelog">ChangeLog</A>
<LI> <A HREF="help.php?q=install">Installing/Upgrading</A>
<LI> <A HREF="help.php?q=readme">ReadMe</A>
<LI> <A HREF="help.php?q=todo">ToDo</A>
<LI> <A HREF="help.php?q=licensing">Licensing</A>
<LI> <A HREF="help.php?q=credits">Credits</A>
<BR /><BR />
<?php
	if ((array_key_exists('q', ($_GET))) && ($_GET['q']== 'tickets')) {

?>
		<FONT CLASS="header"><BR />Background</FONT><BR /><BR />
		<blockquote>
		This version of Tickets started life as Daniel Netz's PHPTicket, a well-regarded Open Source product for tracking user technology issues
		in an academic information technology shop.  Tickets 2.0 built on that foundation to address the needs of dispatch teams who lack the
		benefits of a significant budget - notably volunteer groups - although any 'budget-challenged' team might find its capabilities suitable
		to the team mission.<BR /><BR />
		We extended Tickets to take advantage of the mapping functionality provided by Google Maps, a major addition, and also the ability to
		record information specific to patients handled by dispatch teams.  In addition, a major extension addressed the needs of tracking status
		and location of response units such as emergency medicine.  Mobile units may be tracked where the units broadcast
		location-related information via APRS.
		<BR /><BR />Tickets' technical underpinnings are widely used within the Open Source community, and include PHP as the server-side scripting
		language, Javascript as the client-side equivalent, and MySQL as the database engine.  The mapping engine is Google's API, which seems to
		grow in capabilities  almost weekly.<BR /><BR />
		Despite its capabilities, Tickets won't be suitable for those agencies with a need for 'high-specification' GIS, where life-and-death
		situations occasionally require the highest reliability and accuracy in directing response units via their CAD systems.  But these
		agencies will usually have budgets suitable to high-$ commercial products;  Tickets is designed to meet the needs of an ignored
		market segment, such as teams - often comprised mostly of volunteers - who nontheless need an effective Computer-Aided Dispatch
		tool in meeting mission needs.
		<BR /><BR />In the past, and where any software was used at all, teams too often relied on makeshift adaptations of common office
		products such as spreadsheets - or worse still, to the familiar 'yellow stickies' all over your desk - and it is a tribute to those
		among you who have used these with any effectiveness.  We hope that	with the availabilty of Tickets, your energy and creativeness
		may better be applied.<BR /><BR /></blockquote>

		<FONT CLASS="header">Tickets, Actions, and Patients</FONT><BR /><blockquote>
		A ticket describes a single dispatch run. A given ticket may have any number of actions related to it to describe work in progress or
		adding sidenotes, and are described below.  Similarly, any number of <B><?php print $patient; ?></b> records may be written, each associated with a
		given ticket, and may be used to capture information regarding patients handled by the dispatch team.  A ticket contains several
		information elements describing the dispatch task <?php print $disposition;?>. <B>Issue date</B> defines the date and time
		the ticket was created, <B>problem start</B> and <B>problem end</B> date and time for when the dispatch task starts and ends.
		The <B>scope</B> is now being used for incident description, whereas it had been used differently in the original PHPTicket,.
		The <B>owner</B> field identifies the user who wrote the ticket.<BR /><BR />
		The <B>affected</B> field is not used in this version of Tickets.
		The <B>status</B> field is either open or closed, depending on the dispatch task status. Tickets are closed by changing the status
		value, using the edit form.  Closed tickets may be re-opened by changing the status again. Removed tickets however are deleted
		permanently from the database,  with its related action and patient records.
		The <B>description</B> field describes the ticket in some depth, while the Comments field may be used to record information on the
		item's final <?php print $disposition;?>.<BR /><BR />
		When the issue described in a ticket is updated, <B>action</B> and/or <B>patient</B> records may be written to reflect that
		change, these being largely unstructured values with a date recording the date/time the item was added.<BR /><BR />
		On the main <b>Current Call Tickets</b> screen, colored bullets indentify mobile Units, with the bullet color identifying
		The unit's last reported speed;  red denotes stopped, green denotes a moving unit, and white denotes a rapidly-traveling unit
		(50mph or over).  On that screen, for access to detailed information, click on the sidebar line or else the icon.  Directly
		beneath the map are icons which, when clicked, show only those incidents of the selected urgency - similar to 'layer' displays
		in conventional GIS systems.
		</blockquote>

<?php
		}
	else if ((array_key_exists('q', ($_GET))) && ($_GET['q']== 'config')) {
?>
		<FONT CLASS="header"><BR />Configuration</FONT><BR /><blockquote>
		The configuration section of Tickets provides user access privileges, various settings and database maintenance. User records are created, edited
		and deleted here. The <b>administrator</b> user flag toggles user management rights, i.e. the right to edit user accounts as well as administer the
		database. The <b>optimize</b> function optimizes the database for faster queries. The <b>database reset</b> deletes ticket, action, patient and user rows
		in the database and creates a default "Admin" user with the password <b>admin</b>. It also resets settings to its original state. The
		settings control various variables in Tickets and should be carefully changed since there's limited verification of entered values.
		<BR /><BR />
		Any number of <B>Units</B> may be entered, each, optionally, with a map location. If the unit is identified as mobile, and has a
		call sign, then that call sign is used to capture APRS position information from APRSWorld online. (The '%' meta-character is automatically
		appended to each callsign for the search, so users should not add this character themselves.)</blockquote>

<?php
	}
	else if ((array_key_exists('q', ($_GET))) && ($_GET['q']== 'notify')) {
?>
		<FONT CLASS="header"><BR />Notifies</FONT><BR /><blockquote>
		This feature enables notification of ticket events, currently limited to email. Each notify event consists of
		one email address to which the notification will be sent, a command string to trigger a program or script (not implemented yet)
		and at which ticket changes to notify.<BR /><BR />

		To add a notify event, when viewing the ticket, click the <B>Notify</B> link and fill in the form. To view and/or edit the notifies
		belonging to the logged in user, click the <B>Edit My Notifies</B> under <B>Configuration</B>.</blockquote>

<?php
	}
	else if ((array_key_exists('q', ($_GET))) && ($_GET['q']== 'develop')) {
?>
		<FONT CLASS="header"><BR />Revising</FONT><BR /><blockquote>
		Revising Tickets to suit your particular needs will require that the programmer have a working knowledge
		of PHP,  SQL syntax, Javascript and html. The  PHP code is fairly simple and easy to edit while the HTML and Javascript code that make up the
		interface may be less simple to change. The font properties, table backgrounds	etc. is using CSS (default.css) for easy editing.<BR /><BR />
		Most of the functions are located in the functions.inc.php file. To add a setting, just add the line in the "settings" table in
		the database and it'll show up on the settings screen.  You'll need a database editor like PHPMyAdmin for this.

		<BR /><BR />
		All data is stored in a MySQL database: A table named <b>user</b> provides for simple authentication of users, <b>action</b> and
		<B>patient</b> tables are, respectively, actions and patient data associated with a given ticket, while table <b>ticket</b> contains
		base ticket data. The <b>scope</b> column represents
		ticket type and may be set to any useful value. <b>Issue date</b> is ticket creation date, <b>affected</b>
		is affected systems/entities and <b>status</b> is the ticket status, opened or closed.
		The <b>responder</b> table stores information relating to response units, and holds significant information regarding, optionally,
		on that unit's geographic location and callsign.
		The <b>settings</b> table contains a significant number of settings variables for both cosmetic and functional tailoring to a
		site's needs.<BR /><BR />  A key element here is that of the site's 86-character <b>GMaps API key</b>, which use is mandatory
		with this version of Tickets, and which is freely available from Google.<BR /><BR />
		The <b>tracks</b> table retains information on the most recent APRS position data for those callsigns.<BR /><BR />
		The <b>notify</b> table contains the ticket notifications entered by the users. See help section <b>notifies</b>
		for more info.</blockquote>
<?php
		}
	else if ((array_key_exists('q', ($_GET))) && ($_GET['q']== 'install')) {
?>
		<FONT CLASS="header"><BR />Installing/Upgrading</FONT><BR /><blockquote>
		Tickets is installed and upgraded through <B>install.php</B>. You'll need valid information about the MySQL database installation.
		More info on the install process can be found in <B>install.php</B>.
		<FONT CLASS="warn">WARNING: Do NOT keep <B>install.php</B> accessible to everyone after installation/upgrading.</FONT></blockquote>
<?php
		}
	else if ((array_key_exists('q', ($_GET))) && ($_GET['q']== 'changelog')) {
		print '<PRE>'; readfile('ChangeLog'); print '</PRE>';
		}
	else if ((array_key_exists('q', ($_GET))) && ($_GET['q']== 'readme')) {
		print '<PRE>'; readfile('README.txt'); print '</PRE>';
		}
	else if ((array_key_exists('q', ($_GET))) && ($_GET['q']== 'todo')) {
		print '<PRE>'; readfile('TODO.txt'); print '</PRE>';
		}
	else if ((array_key_exists('q', ($_GET))) && ($_GET['q']== 'licensing')) {
		print '<PRE>'; readfile('COPYING.txt'); print '</PRE>';
		}
	else if ((array_key_exists('q', ($_GET))) && ($_GET['q']== 'credits')) {
?>
		<blockquote>
		<FONT CLASS="header"><BR />Credits</FONT><BR />
		While Version 2 was initially programmed by Arnie Shore, shoreas at gmail dot com, Andy Harvey joined us in early '09, and in
		addition to programming skills, brought considerable experience as a hands-on user. Much of what you see in Tickets today (Spring '11) is his work.  <br />
		Alan Jump has contributed a sorely-needed user manual, an under-appreciated component of any system that makes any claims to user-friendliness and ease-of-use.<br />
		And, certainly the thoughts, ideas and suggestions from our users have also been key contributors to the progress we've made.  Thanks, folks.<BR /><BR />
		Programming of the base version of Tickets was by Daniel Netz, netz "at" home "dot" se</A><BR />
		Base version SourceForge Project: <A HREF="http://www.sourceforge.net/projects/ticket/" target="new">sourceforge.net/projects/ticket/<BR />
		Base version CSV Repository: <A HREF="http://cvs.sourceforge.net/cgi-bin/viewcvs.cgi/ticket/" target="new">cvs.sourceforge.net/cgi-bin/viewcvs.cgi/ticket/</A><BR />
		Tickets is licensed under <A HREF="COPYING" target="new">GPL</A>.<BR />
		Thanks to <A HREF="http://www.apache.org" TARGET="new">Apache</A>, <A HREF="http://www.php.net" TARGET="new">PHP</A>, <A HREF="http://www.mysql.com" TARGET="new">MySQL</A>, <A HREF="http://www.phpedit.com" TARGET="new">PHPEdit</A> and OpenSource in all.<BR />
		Special thanks to everyone contributing with ideas, code snippets and reporting problems.</blockquote>
<?php
		}
?>
</BODY></HTML>
