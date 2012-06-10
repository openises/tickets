<?php
// displays all the file nodes
if(!$xml=simplexml_load_file('users.xml')){
    trigger_error('Error reading XML file',E_USER_ERROR);
	}
echo 'Displaying contents of XML file...<br />';
foreach($xml as $user){
    echo 'Name: '.$user->name.' Address: '.$user->address.'Email: '.$user->email.'<br />';
}
?>