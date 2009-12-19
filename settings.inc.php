<?php

/* This file accomodates your installation.  This is the *ONLY* irving file that should need revision. */

/* database stuff from here - MUST  be changed */

require_once('./incs/mysql.inc.php');			// database settings - edit per yr installation

$key_str			= "_id";			// FOREIGN KEY (parent_id) REFERENCES parent(id) relationship terminal string identifier 
//										e.g., if field 'sometable_id' relates to table 'sometable' then string '_id' is the $FK_id value

/* cosmetic stuff from here - MAY  be changed */

$irving_title		= "Firehouse - An Open Source FD Dispatch System";
$rowsPerPage		= 20;				// determines number of rows displayed per page in listing
$showblobastext		= TRUE;				// change to FALSE if blobs are not to be displayed
$date_out_format	= 'Y-m-d H:i';		// well, date format - per php date syntax
//$date_out_format	= 'n/j/y H:i';		// ex: 5/25/06
$date_in_format		= 0;					// yyyy-mm-dd, per mMySQL standard
$links_col			= 0;				// in the listing display, this column sees the View/Edit/Delete function links
$text_type_max		= 90;				// text input fields exceeding this size limit will be treated as <textarea>
$text_list_max		= 32;				// text input fields exceeding this size limit will be treated as <textarea>
$fill_from_last		= FALSE;			// if set to TRUE, new recrods are populated from last created
$doUTM				= FALSE;			// if set, coord displays UTM
$istest 			= TRUE;				// if set to TRUE, displays form variables for trouble-shooting atope each loaded page

/* maps irv_settings for use IF you are implementing maps */

$maps 				= TRUE;
$api_key			= "ABQIAAAAiLlX5dJnXCkZR5Yil2cQ5BQOqXXamPs-BOuxLXsFgzG1vgHGdBTx978MQ0RymVQmZOPJN5XuAFdftw";	// AS local opensara

$def_state			= "10";				// Florida
$def_county			= "58";				// Sarasota
$def_lat			= NULL;				// default center lattitude - if present, overrides county centroid 
$def_lon			= NULL;				// guess!
$radius				= 10;				// radius of circle on default center (miles)
$do_hints			= FALSE;			// if true, print data hints at input fields
?>
