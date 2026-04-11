<?php
require_once('../incs/functions.inc.php');
@session_start();

do_login(basename(__FILE__));

$all_id = sanitize_int($_GET['all_id']);

$query_all = "SELECT * FROM `{$GLOBALS['mysql_prefix']}allocations` WHERE `id` = ?";
$result_all = db_query($query_all, [$all_id]);$row_all = $result_all ? $result_all->fetch_array() : null;

$id = $row_all['member_id'];

$query    = "SELECT *, UNIX_TIMESTAMP(_on) AS `_on` FROM `{$GLOBALS['mysql_prefix']}member` `m`
    WHERE `m`.`id`=? LIMIT 1";
$result = db_query($query, [$id]);$row = $result ? stripslashes_deep($result->fetch_assoc()) : null;
?>
<LINK REL=StyleSheet HREF="../stylesheet.php?version=<?php print time();?>" TYPE="text/css">
<script src="../js/jquery-1.5.2.min.js" type="text/javascript"></script>
<script type="text/javascript" src="../js/jquery.textarea-expander.js"></script>
<script src="../js/misc_function.js" type="text/javascript"></script>
<SCRIPT TYPE="application/x-javascript" SRC="../js/jss.js"></SCRIPT>
<SCRIPT>
window.onresize=function(){set_size()};
var viewportwidth, viewportheight, outerwidth, outerheight, colwidth;

function set_size() {
    if (typeof window.innerWidth != 'undefined') {
        viewportwidth = window.innerWidth,
        viewportheight = window.innerHeight
        } else if (typeof document.documentElement != 'undefined'    && typeof document.documentElement.clientWidth != 'undefined' && document.documentElement.clientWidth != 0) {
        viewportwidth = document.documentElement.clientWidth,
        viewportheight = document.documentElement.clientHeight
        } else {
        viewportwidth = document.getElementsByTagName('body')[0].clientWidth,
        viewportheight = document.getElementsByTagName('body')[0].clientHeight
        }
    set_fontsizes(viewportwidth, "popup");
    outerwidth = viewportwidth * .98;
    outerheight = viewportheight * .95;
    colwidth = viewportwidth * .93;
    if($('outer')) {$('outer').style.width = outerwidth + "px";}
    if($('outer')) {$('outer').style.height = outerheight + "px";}
    if($('leftcol')) {$('leftcol').style.width = colwidth + "px";}
    }
</SCRIPT>
</HEAD>
<BODY>
<?php
    $query2 = "SELECT * FROM `{$GLOBALS['mysql_prefix']}equipment_types` WHERE `id` = ? LIMIT 1";
    $result2 = db_query($query2, [$row_all['skill_id']]);    $row2 = $result2 ? stripslashes_deep($result2->fetch_assoc()) : null;
    $description = $row2['description'];
    $name = $row2['name'];
    $spec = $row2['spec'];
    $serial = $row2['serial'];
    $condition = $row2['condition'];
?>
    <DIV id = "outer" style='position: absolute; left: 0px; width: 90%;'>
        <DIV id = "leftcol" style='position: relative; left: 30px; top: 70px; float: left;'>
            <DIV id='button_bar' class='but_container'>
                <SPAN CLASS='heading' STYLE='text-align: center; display: inline; font-size: 1.5em;'>View <?php print get_text('Equipment');?> Issued to "<?php print e($row['field2']);?> <?php print e($row['field1']);?>"</SPAN>
                <SPAN id='can_but' CLASS='plain text' style='width: 80px; display: inline-block; float: right;' onMouseover='do_hover(this.id);' onMouseout='do_plain(this.id);' onClick="window.close();"><SPAN STYLE='float: left;'><?php print get_text("Close");?></SPAN><IMG STYLE='float: right;' SRC='../images/close.png' BORDER=0></SPAN>
            </DIV>
            <FORM METHOD="POST" NAME= "equip_edit_Form" ACTION="member.php?func=member&goeditequip=true">
                <FIELDSET>
                <LEGEND><?php print get_text('Equipment');?> Issued</LEGEND>
                    <BR />
                    <LABEL><?php print get_text('Equipment');?> Issued:</LABEL>
                        <DIV CLASS='td_data_wrap'><?php print e($name);?></DIV>
                    <BR />
                    <LABEL><?php print get_text('Description');?>:</LABEL>
                    <DIV class='td_data_wrap'><?php print e($description);?></DIV>
                    <BR />
                    <LABEL><?php print get_text('Spec');?>:</LABEL>
                    <DIV class='td_data_wrap'><?php print $spec;?></DIV>
                    <BR />
                    <LABEL><?php print get_text('Serial Number');?>:</LABEL>
                    <DIV class='td_data_wrap'><?php print $serial;?></DIV>
                    <BR />
                    <LABEL><?php print get_text('Condition');?>:</LABEL>
                    <DIV class='td_data_wrap'><?php print $condition;?></DIV>
                    <BR />
                </FIELDSET>
            </FORM>
        </DIV>
    </DIV>
    <FORM NAME='can_Form' METHOD="post" ACTION = "member.php?func=member&view=true&id=<?php print e($id);?>"></FORM>
</BODY>
<SCRIPT>
if (typeof window.innerWidth != 'undefined') {
    viewportwidth = window.innerWidth,
    viewportheight = window.innerHeight
    } else if (typeof document.documentElement != 'undefined'    && typeof document.documentElement.clientWidth != 'undefined' && document.documentElement.clientWidth != 0) {
    viewportwidth = document.documentElement.clientWidth,
    viewportheight = document.documentElement.clientHeight
    } else {
    viewportwidth = document.getElementsByTagName('body')[0].clientWidth,
    viewportheight = document.getElementsByTagName('body')[0].clientHeight
    }
set_fontsizes(viewportwidth, "popup");
outerwidth = viewportwidth * .98;
outerheight = viewportheight * .95;
colwidth = viewportwidth * .93;
if($('outer')) {$('outer').style.width = outerwidth + "px";}
if($('outer')) {$('outer').style.height = outerheight + "px";}
if($('leftcol')) {$('leftcol').style.width = colwidth + "px";}
</SCRIPT>
</HTML>