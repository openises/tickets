<?php
function the_func() {
    return false;
    }
if ($temp = the_func()) {
    print __LINE__;
    print $temp;
    }
else {
    print __LINE__;
    }
?>