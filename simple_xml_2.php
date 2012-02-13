<?php
// displays all the file nodes
if(!$xml=simplexml_load_file('wxalert.xml')){
    trigger_error('Error reading XML file',E_USER_ERROR);
	}
echo 'Displaying contents of XML file...<br />';
foreach($xml as $user){
    echo 'NOTE: '.$user->note . '<br>HEADLINE: '.$user->headline . '<br>DESCRIPTION: ' . $user->description.'<br />';
}
?>