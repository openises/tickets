<?php
	mysql_query("DELETE FROM `user` WHERE `user`='admin'");
	mysql_query("INSERT INTO `user` (user,passwd,info,level,ticket_per_page,sort_desc,sortorder,reporting) VALUES('admin',PASSWORD('admin'),'Administrator',1,0,1,'date',0)") or die("INSERT INTO user failed, execution halted");
	print "<LI> Created user '<B>admin</B>'";
?>