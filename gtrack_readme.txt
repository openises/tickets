Notes re setting up a Gtrack server and integrating with Tickets V2.10 G:

1.	Gtrack is a web based application written in PHP and Javascript. It requires you to set it up on a server and can either be separate or
	co-located with the Tickets installation.

2.	Gtrack is similar in functionality to LocateA however LocateA is a hosted application and therefore doesn't require any setup other than
	getting an account and an ID. The advantage of Gtrack is that you have ultimate control over who can see the location of your units.
	LocateA requires that unit location is shared on a public map. This is the case for Gtrack as well however that public map may have limited access.

3.	Gtrack requires that Ion Cube (www.ioncube.com) be loaded on the server, either as a PHP moduled or the runtime located in the same directory as the
	main Gtrack application.

4.	Gtrack must be installed in a root web directory (i.e. not in a subdirectory off the main directory such as www.yourserver.com/gtrack). An alternative
	is to use a subdomain pointed to the sub-directory. An example that will work is that gtrack is accessed by www.tracking.yourserver.com that points to 
	the gtrack subdirectory under the main root web directory.

5.	Gtrack supports the following devices:

	Windows Mobile 5 and 6
	Java Mobile Phones
	Android Mobile Phones (in beta)
	Windows 2K and XP based devices (laptops etc)

6.	Once the Gtrack server is built and tested (it has it's own map display that allows you to test and view device locations), it can be integrated into
	Tickets by entering the URL of the Gtrack installation (including http://) into the configuration page. It can then be tested by using the test script
	also accessible from the configuration page. You will need to know the tracking_id/user_id of the device you want to fetch the location from. You will
	need to make sure that for the user, "yes" is selected for Public / Share Data.

7.	Please note that when you set up a user on Gtrack, a user_id is automatically assigned. This is the user_id that needs to be entered in the test page ]
	and also the callsign field in the unit add/edit page in Tickets.

NB.	You can also manually check the page on Gtrack where the data is captured from. This is achieved by opening a browser window and going to:
	http://www.yourgtrackurl.com/data.php?userid=XXX where XXX is repalaced by the user_id of the unit you wnat to check. Data from Gtrack (and also
	LocateA) is captured from xml output by the data.php page.

For more information on Gtrack go to www.gtrack.co.uk

July 29, 09
Arnie Shore
Andy Harvey, UK
