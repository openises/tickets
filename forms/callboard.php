<?php
require_once __DIR__ . '/../incs/functions.inc.php';
require_login_or_401();

?>
    <BODY style='overflow-y: auto;'>
    <SCRIPT TYPE="application/x-javascript" src="./js/wz_tooltip.js"></SCRIPT>
        <DIV id = "outer" style='position: absolute; left: 10px; top: 10px; height: 500px; width: auto;'>
            <DIV class="scrollableContainer" id='board'>
                <DIV class="scrollingArea" id='the_board'><CENTER><IMG src='./images/owmloading.gif'></CENTER></DIV>
            </DIV>
        </DIV>
    </BODY>

