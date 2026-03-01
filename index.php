<?php
/*
10/8/07 added domain detection for GMaps API key association
1/8/08 added settings email_reply_to' and call_board;
3/20/08 added settings map height and width;
6/7/08  added kml files setting
6/9/08  revised default 'admin' account level to 'super'
6/12/08 revised version number only
6/27/08 revised version number only
6/28/08 revised version number only
7/16/08 revised default military time
9/13/08 added error_reporting, lat/lng setting entry
9/13/08 added table `unit_types`
9/13/08 added white pages key
9/13/08 GSearch API key
9/15/08 added tables 'login'
9/16/08 corr's to undefined's
9/17/08 dropped field 'hash', rearranged field 'user' in table 'user'
9/17/08 added table 'photos'
9/18/08 added table `cities`
9/22/08 version # only
10/08/08 version # only
10/11/08 major schema update
10/17/08 added '__sleep' setting
10/19/08 added pager nos. to responder
10/22/08 expanded table notify schema
11/6/08 table user default corrections, sql_mode
1/17/09 changed `in-quarters` to `on_scene`, insert `auto_route` settings
1/17/09 version no. to '2.9 B beta'
1/18/08 added 'in service' unit status
1/25/09 team tables renamed
1/27/09 added default area code setting, unit_types schema, responder schema updates
2/3/09 version no.
2/8/09 table tracks_hh added
2/11/09 session table expanded
2/14/09 session flags to varchar
2/21/09 check file write-able
2/24/09 added 'terrain' setting
3/17/09 chgd aprs_poll to auto_poll
3/22/09 removed redundant def_area_code
4/10/09 responder schema update
1/23/10 - removed table session
8/5/10 version number base - to permit index.php to update schema, internet setting added
8/8/10    accomodate absent mysql.inc.php - as install trigger
10/29/10  'PASSWORD' => 'MD5' to accommodate old MySQL versions
12/18/10   write permissions test corrected
1/10/11 Added default setting for Group or dispatch
5/11/12 Added code for quick start.
1/9/2013 API key is no longer mandatory
4/2/2013 removed API key value.
3/1/2026 index.php reduced to startup routing only; all schema/install/upgrade logic is handled in install.php with centralized versions metadata.
*/
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE & ~E_WARNING);
session_start();

require_once __DIR__ . '/incs/versions.inc.php';

$versions = tickets_get_versions();
$installPath = __DIR__ . '/install.php';
$installExists = file_exists($installPath);

if ($installExists && $versions['installed'] !== null && !$versions['match']) {
    header('Location: install.php');
    exit();
}

header('Location: main.php');
exit();
