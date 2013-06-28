<?php
//$dir = "./tickets_2013_apr_02_work";	//getcwd
$dir = getcwd();	//getcwd
$findex = array();
$findex['path'] = array();
$findex['file'] = array();
$extensions = array('.cfm','.html','.htm','.css','.php','.gif','.jpg','.png','.jpeg','.dwt','.js');
$excludes = array('.svn');
function rec_scandir($dir)
	{
	$files = array();
	global $findex;
	global $extensions;
	global $excludes;
	if ( $handle = opendir($dir) )
	{
	$found = false;
	while ( ($file = readdir($handle)) !== false )
		{
		if ( $file != ".." && $file != "." )
			{
		if ( is_dir($dir . "/" . $file) )
			{
			$files[$file] = rec_scandir($dir . "/" . $file);
			}
		else
			{
			for ($i=0;$i<sizeof($extensions);$i++)
				{
				if (strpos(strtolower($file),strtolower($extensions[$i])) > 0)
					{
				$found = true;
				}
				}
			for ($i=0;$i<sizeof($excludes);$i++)
				{
				if (strpos(strtolower($file),strtolower($excludes[$i])) > 0)
					{
				$found = false;
				}
				}
			if ($found)
				{
				$files[] = $file;
				$dirlink = $dir . "/" . $file;
				array_push($findex['path'],$dirlink);
				array_push($findex['file'],$file);
				}
			$found = false;
			}
		   		}
			}
		closedir($handle);
		return $findex;
		}
		}

echo "\n";
echo " Searching ". $dir ." for matching files\n";
$files = rec_scandir($dir);
echo " Found " . sizeof($files['file']) . " matching extensions<br />\n";
echo " Scanning for orphaned files....<br />\n";
$findex['found'] = array();
for ($i=0;$i<sizeof($findex['path']);$i++)
	{
	echo $i . " ";
	$contents = file_get_contents($findex['path'][$i]);
	for ($j=0;$j<sizeof($findex['file']);$j++)
		{
		if (strpos($contents,$findex['file'][$j]) > 0)
			{
			@$findex[found][$j] = 1;
			}
		}
	}
echo "\n";
$counter=1;
for ($i=0;$i<sizeof($findex['path']);$i++)
	{
	if (@$findex[found][$i] != 1)
		{
		echo  "<br /> " . $counter . ") " .  substr($findex['path'][$i],0,1000) . " is orphaned\n";
		$counter++;
		}
	}
?>