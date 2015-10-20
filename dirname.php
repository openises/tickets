<?php
print $_SERVER['DOCUMENT_ROOT'];
$filestore = explode("/", $_SERVER['DOCUMENT_ROOT']);
print end($filestore);
?>