function add_header($ticket_id)		{/* add header with links */
	print "<BR /><NOBR><FONT SIZE='2'>This Ticket: ";	
	if (!is_guest()){
		print "<A HREF='edit.php?id=$ticket_id'>Edit </A> | ";
		print "<A HREF='edit.php?id=$ticket_id&delete=1'>Delete </A> | ";
		if (!is_closed($ticket_id)) {
			print "<A HREF='action.php?ticket_id=$ticket_id'>Add Action</A> | ";
			print "<A HREF='patient.php?ticket_id=$ticket_id'>Add Patient</A> | ";
			}
		print "<A HREF='config.php?notify=true&id=$ticket_id'>Notify</A> | ";
		}
	print "<A HREF='main.php?print=true&id=$ticket_id'>Print </A> | ";
	print "<A HREF='routes.php?ticket_id=$ticket_id'>Responders</A></FONT></NOBR><BR />  ";		// new 9/22
	}
