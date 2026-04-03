<?php
if ( !defined( 'E_DEPRECATED' ) ) { define( 'E_DEPRECATED',8192 );}
error_reporting (E_ALL  ^ E_DEPRECATED);
require_once('../incs/functions.inc.php');        // resides in ./ajax -- get_addrs.php

// snap(basename(__FILE__), __LINE__);

$js_func = "do_selected_addr";                        // client-side js span 'onclick' function
$q = strtoupper ( trim(sanitize_string($_POST["q"])) );                // keyboard to $_POST['']
$q_len = safe_strlen($q);
$limit = 10;
$q_like = $q . '%';

$which = get_variable("addr_source");
switch (intval($which)) {
    case 99 :        // jc_911
        $tablename = "jc_911";
        $query = "SELECT
                    CONCAT_WS( '/',`id`, lat, lng, `house_num` , UPPER(`rd_name`) , UPPER(`community`) )     AS `payload` ,
                    CONCAT_WS( ' ', `house_num` , UPPER(`rd_name`) , UPPER(`community`) )                     AS `address` ,
                    CONCAT_WS( ' ', `old_num` , UPPER(`old_rd_name`) , UPPER(`community`) )                 AS `address_old`
                FROM `$GLOBALS[mysql_prefix]{$tablename}`
                WHERE (
                    CONCAT_WS( ' ', `house_num` , UPPER(`rd_name`) , UPPER(`community`) ) LIKE UPPER( ? )
                    OR CONCAT_WS( ' ', `old_num` , UPPER(`old_rd_name`) , UPPER(`community`) ) LIKE UPPER( ? )
                    )
                ORDER BY `address` ASC, `address_old` ASC LIMIT {$limit}";

//        dump ($query);
        $result = db_query($query, [$q_like, $q_like]) or do_error($query, $query, '', basename(__FILE__), __LINE__);

        $outstr = "";
        while ($row = stripslashes_deep($result->fetch_assoc()))     {
            extract ($row);
            if (substr ( $address, 0, $q_len ) == $q ) {             // match user input to $address?
                $outstr.="<br><span><input type = radio name = 'addr_rb' onclick = '{$js_func}(\"{$payload}\")'>{$address}</span>\n";        // call client-side function
                }
            if (substr ( $address_old, 0, $q_len ) == $q ) {        // $address_old stuff
                $outstr.="<br><span><input type = radio name = 'addr_rb' onclick = '{$js_func}(\"{$payload}\")'>{$address_old}</span>\n";    // call client-side function
                }
            }            // end while ($row)

        echo ($outstr == "") ? "<span>No suggestion!</span>\n": $outstr ;     // finished - output the response
//        dump ($outstr);
        break;        // end case 99

    case 1:                // tickets -
        $tablename = "ticket";
        $query = "SELECT
                    UPPER(`street`)                             AS `address`,
                    CONCAT_WS( '/',`id`, ROUND(`lat`,6), ROUND(`lng`,6), `street`, `address_about` , `city` )    AS `payload`
                FROM `$GLOBALS[mysql_prefix]{$tablename}`
                WHERE ( UPPER(`street`) LIKE UPPER( ? ) )
                ORDER BY `address` ASC LIMIT {$limit}";

//        dump ($query);
        $result = db_query($query, [$q_like]) or do_error($query, $query, '', basename(__FILE__), __LINE__);

        $outstr = "";
        while ($row = stripslashes_deep($result->fetch_assoc()))     {
            extract ($row);
            if (substr ( $address, 0, $q_len ) == $q ) {             // match user input to $address?
                $outstr.="<br><span><input type = radio name = 'addr_rb' onclick = '{$js_func}(\"{$payload}\")'>{$address}</span>\n";        // call client-side function
                }
            }            // end while ($row)

//        dump ($outstr);
        echo ($outstr == "") ? "<span>No suggestion!</span>\n": $outstr ;     // finished - output the response
        break;        // end case 1

    case 2:
        $tablename = "constituents";                        // new community, post_code, reference
        $query = "SELECT
                    CONCAT_WS( '/',`id`, lat, lng, `street`, `community`, `city`  ) AS `payload` ,
                    CONCAT_WS( ' ', UPPER(`street`), apartment )                    AS `address`
                FROM `$GLOBALS[mysql_prefix]{$tablename}`
                WHERE ( UPPER(`street`) LIKE UPPER( ? ) )
                ORDER BY `address` ASC LIMIT {$limit}";

//        dump ($query);
        $result = db_query($query, [$q_like]) or do_error($query, $query, '', basename(__FILE__), __LINE__);

        $outstr = "";
        while ($row = stripslashes_deep($result->fetch_assoc()))     {
            extract ($row);
            if (substr ( $address, 0, $q_len ) == $q ) {             // match user input to $address?
                $outstr.="<br><span><input type = radio name = 'addr_rb' onclick = '{$js_func}(\"{$payload}\")'>{$address}</span>\n";        // call client-side function
                }
            }            // end while ($row)

//        dump ($outstr);
        echo ($outstr == "") ? "<span>No suggestion!</span>\n": $outstr ;     // finished - output the response
        break;        // end case 1

    default:
        dump("ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR ");
        }        // end switch()
?>
