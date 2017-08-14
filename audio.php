<?php
require_once('incs/functions.inc.php');		//7/28/10
$wavcount = 0;
$mp3count = 0;
$midcount = 0;
$counter = 0;
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2 Final//EN">
<HTML>
<HEAD>
<TITLE>Sounds</TITLE>
<META NAME="Author" CONTENT="">
<META NAME="Keywords" CONTENT="">
<META NAME="Description" CONTENT="">
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
<META HTTP-EQUIV="Expires" CONTENT="0">
<META HTTP-EQUIV="Cache-Control" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Pragma" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Content-Script-Type"	CONTENT="application/x-javascript">
<LINK REL=StyleSheet HREF="stylesheet.php?version=<?php print time();?>" TYPE="text/css">	<!-- 3/15/11 -->
<SCRIPT TYPE="application/x-javascript" SRC="./js/jss.js"></SCRIPT>
<SCRIPT SRC="./js/misc_function.js" TYPE="application/x-javascript"></SCRIPT>
<SCRIPT>
var viewportwidth;
var viewportheight;
var outerwidth;
var outerheight;

function playit(n) {	// 6/12/10
	try 		{document.getElementsByTagName('audio')[n].play();}
	catch (e) 	{alert('Not supported');}		// ignore
	}				// end function playit()

</SCRIPT>
</HEAD>
<BODY>
<DIV ID='outer' style='position: absolute; top: 0px; left: 0px;'>
	<DIV id='button_bar' class='but_container'>
		<SPAN CLASS='heading' STYLE='text-align: center; display: inline; font-size: 1.5em;'>Audio Test</SPAN>
		<SPAN ID='fin_but' class='plain text' style='float: right; width: 100px;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick='window.close();'><SPAN STYLE='float: left;'><?php print get_text("Finished");?></SPAN><IMG STYLE='float: right;' SRC='./images/finished_small.png' BORDER=0></SPAN>
	</DIV>
	<DIV id='inner' style='width: 100%; position: relative; top: 60px;'>
		<?php if(file_exists('./sounds/4bells.wav')) {print '<audio src="./sounds/4bells.wav" preload></audio>'; $wavcount++;}?>
		<?php if(file_exists('./sounds/aooga.wav')) {print '<audio src="./sounds/aooga.wav" preload></audio>'; $wavcount++;}?>
		<?php if(file_exists('./sounds/divalarm.wav')) {print '<audio src="./sounds/divalarm.wav" preload></audio>'; $wavcount++;}?>
		<?php if(file_exists('./sounds/door-squeak-1.wav')) {print '<audio src="./sounds/door-squeak-1.wav" preload></audio>'; $wavcount++;}?>
		<?php if(file_exists('./sounds/flushtoilet.wav')) {print '<audio src="./sounds/flushtoilet.wav" preload></audio>'; $wavcount++;}?>
		<?php if(file_exists('./sounds/giggle.wav')) {print '<audio src="./sounds/giggle.wav" preload></audio>'; $wavcount++;}?>
		<?php if(file_exists('./sounds/sonar.wav')) {print '<audio src="./sounds/sonar.wav" preload></audio>'; $wavcount++;}?>
		<?php if(file_exists('./sounds/taps.wav')) {print '<audio src="./sounds/taps.wav" preload></audio>'; $wavcount++;}?>
		<?php if(file_exists('./sounds/bass_1.mid')) {print '<audio src="./sounds/bass_1.mid" preload></audio>'; $midcount++;}?>
		<?php if(file_exists('./sounds/firetrucksiren.mp3')) {print '<audio src="./sounds/firetrucksiren.mp3" preload></audio>'; $mp3count++;}?>
		<?php if(file_exists('./sounds/klaxon.mp3')) {print '<audio src="./sounds/klaxon.mp3" preload></audio>'; $wavcount++;}?>
		<?php if(file_exists('./sounds/missle.mp3')) {print '<audio src="./sounds/missle.mp3" preload></audio>'; $mp3count++;}?>
		<?php if(file_exists('./sounds/phonesring.mp3')) {print '<audio src="./sounds/phonesring.mp3" preload></audio>'; $mp3count++;}?>
		<?php if(file_exists('./sounds/shockperson.mp3')) {print '<audio src="./sounds/shockperson.mp3" preload></audio>'; $mp3count++;}?>
		<?php if(file_exists('./sounds/weatherwarning.mp3')) {print '<audio src="./sounds/weatherwarning.mp3" preload></audio>'; $mp3count++;}?>
		<?php if(file_exists('./sounds/whistlewolf.mp3')) {print '<audio src="./sounds/whistlewolf.mp3" preload></audio>'; $mp3count++;}?>
		<BR />
		<BR />
		<BR />
		<DIV CLASS='header' STYLE='width: 100%; text-align: center; display: block;'>Available Sound Effects For Notification - <u>click below to try</u></DIV><BR /><BR />
<?php 
		if($wavcount > 0) {
?>
			<SPAN CLASS='header' STYLE='text-align: left; display: inline-block; position: relative; left: 10px;'>wav format sound files (Firefox 3.5+ and Safari 5.0+ )</SPAN><BR /><BR />
<?php
			}
?>
		<DIV id='leftcol' style='width: 350px; position: relative; left: 20px;'>
<?php 
			if(file_exists('./sounds/4bells.wav')) {
				print "<SPAN id='w" . $counter . "' class='plain' style='float: left; width: 350px; display: block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick = 'playit(" . $counter . ");'>4 Bells (4bells.wav)</SPAN>";
				$counter++;
				}
			if(file_exists('./sounds/aooga.wav')) {
				print "<SPAN id='w" . $counter . "' class='plain' style='float: left; width: 350px; display: block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick = 'playit(" . $counter . ");'>Aooga (aooga.wav)</SPAN>";
				$counter++;
				}
			if(file_exists('./sounds/divalarm.wav')) {
				print "<SPAN id='w" . $counter . "' class='plain' style='float: left; width: 350px; display: block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick = 'playit(" . $counter . ");'>Dive alarm (divalarm.wav)</SPAN>";
				$counter++;
				}
			if(file_exists('./sounds/door-squeak-1.wav')) {
				print "<SPAN id='w" . $counter . "' class='plain' style='float: left; width: 350px; display: block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick = 'playit(" . $counter . ");'>Door squeak (door-squeak-1.wav)</SPAN>";
				$counter++;
				}
			if(file_exists('./sounds/flushtoilet.wav')) {
				print "<SPAN id='w" . $counter . "' class='plain' style='float: left; width: 350px; display: block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick = 'playit(" . $counter . ");'>Flush toilet (flushtoilet.wav)</SPAN>";
				$counter++;
				}
			if(file_exists('./sounds/giggle.wav')) {
				print "<SPAN id='w" . $counter . "' class='plain' style='float: left; width: 350px; display: block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick = 'playit(" . $counter . ");'>Giggle (giggle.wav)</SPAN>";
				$counter++;
				}
			if(file_exists('./sounds/sonar.wav')) {
				print "<SPAN id='w" . $counter . "' class='plain' style='float: left; width: 350px; display: block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick = 'playit(" . $counter . ");'>Sonar (sonar.wav)</SPAN>";
				$counter++;
				}
			if(file_exists('./sounds/taps.wav')) {
				print "<SPAN id='w" . $counter . "' class='plain' style='float: left; width: 350px; display: block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick = 'playit(" . $counter . ");'>Taps (taps.wav)</SPAN>";
				$counter++;
				}
?>
		</DIV>
		<DIV id='spreader' style='width: 100%; height: 20px; display: inline-block; position: relative: top: 10px;'>&nbsp;</DIV>
<?php 
		if($mp3count > 0) {
?>
			<SPAN CLASS='header' STYLE='text-align: left; display: inline-block; position: relative; left: 10px;'>MIDI format sound files (Chrome 5.0+ and Safari 5.0+ )</SPAN>
<?php
			}
?>
		<BR />
		<BR />
		<DIV id='leftcol2' style='width: 350px; position: relative; left: 20px;'>
<?php
			if(file_exists('./sounds/bass_1.mid')) {
				print "<SPAN id='m" . $counter . "' class='plain' style='float: left; width: 350px; display: block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick = 'playit(" . $counter . ");'>Bass (bass_1.mid)</SPAN>";
				$counter++;
				}
?>
		</DIV>		
		
		<DIV id='spreader2' style='width: 100%; height: 20px; display: inline-block; position: relative: top: 10px;'>&nbsp;</DIV>
<?php 
		if($mp3count > 0) {
?>
			<SPAN CLASS='header' STYLE='text-align: left; display: inline-block; position: relative; left: 10px;'>mp3 format sound files (Chrome 5.0+ and Safari 5.0+ )</SPAN>
<?php
			}
?>
		<BR />
		<BR />
		<DIV id='leftcol3' style='width: 350px; position: relative; left: 20px;'>
<?php
			if(file_exists('./sounds/firetrucksiren.mp3')) {
				print "<SPAN id='m" . $counter . "' class='plain' style='float: left; width: 350px; display: block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick = 'playit(" . $counter . ");'>Fire truck siren (firetrucksiren.mp3)</SPAN>";
				$counter++;
				}
			if(file_exists('./sounds/klaxon.mp3')) {
				print "<SPAN id='m" . $counter . "' class='plain' style='float: left; width: 350px; display: block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick = 'playit(" . $counter . ");'>Klaxon (klaxon.mp3)</SPAN>";
				$counter++;
				}
			if(file_exists('./sounds/missle.mp3')) {
				print "<SPAN id='m" . $counter . "' class='plain' style='float: left; width: 350px; display: block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick = 'playit(" . $counter . ");'>Missile (missle.mp3)</SPAN>";
				$counter++;
				}
			if(file_exists('./sounds/phonesring.mp3')) {
				print "<SPAN id='m" . $counter . "' class='plain' style='float: left; width: 350px; display: block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick = 'playit(" . $counter . ");'>Phone ring (phonesring.mp3)</SPAN>";
				$counter++;
				}
			if(file_exists('./sounds/shockperson.mp3')) {
				print "<SPAN id='m" . $counter . "' class='plain' style='float: left; width: 350px; display: block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick = 'playit(" . $counter . ");'>Electric shock (shockperson.mp3)</SPAN>";
				$counter++;
				}
			if(file_exists('./sounds/weatherwarning.mp3')) {
				print "<SPAN id='m" . $counter . "' class='plain' style='float: left; width: 350px; display: block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick = 'playit(" . $counter . ");'>Weather warning (weatherwarning.mp3)</SPAN>";
				$counter++;
				}
			if(file_exists('./sounds/whistlewolf.mp3')) {
				print "<SPAN id='m" . $counter . "' class='plain' style='float: left; width: 350px; display: block;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick = 'playit(" . $counter . ");'>Wolf whistle (whistlewolf.mp3)</SPAN>";
				$counter++;
				}
?>
		</DIV>
	</DIV>
</DIV>
</BODY>
<SCRIPT LANGUAGE="Javascript">

if (typeof window.innerWidth != 'undefined') {
	viewportwidth = window.innerWidth,
	viewportheight = window.innerHeight
	} else if (typeof document.documentElement != 'undefined'	&& typeof document.documentElement.clientWidth != 'undefined' && document.documentElement.clientWidth != 0) {
	viewportwidth = document.documentElement.clientWidth,
	viewportheight = document.documentElement.clientHeight
	} else {
	viewportwidth = document.getElementsByTagName('body')[0].clientWidth,
	viewportheight = document.getElementsByTagName('body')[0].clientHeight
	}
	
set_fontsizes(viewportwidth, "popup");
outerwidth = viewportwidth * .99;
outerheight = viewportheight * .95;
$('outer').style.width = outerwidth + "px";
$('outer').style.height = outerheight + "px";
$('inner').style.width = outerwidth + "px";
$('inner').style.height = outerheight + "px";
</SCRIPT>
</HTML>
