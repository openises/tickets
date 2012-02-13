<?php
/*
*/
error_reporting(E_ALL);
require_once('./incs/functions.inc.php');
?>


$our_string = '{
   "listings" : [
      {
         "listingmeta" : {
            "moreinfolinks" : {
               "mapareacode" : {
                  "linktext" : "View Area Code Map",
                  "url" : "http://www.whitepages.com/16176/track/10217/interstitial/maps/MD/?npa=410"
               }
            }
         },
         "geodata" : {
            "longitude" : "-76.5325",
            "latitude" : "38.9915",
            "geoprecision" : 6
         },
         "address" : {
            "city" : "Annapolis",
            "deliverable" : "false",
            "state" : "MD"
         },
         "phonenumbers" : [
            {
               "areacode" : "410",
               "linenumber" : "3986",
               "carrier" : "Verizon Wireless",
               "fullphone" : "(410) 353-3986",
               "type" : "mobile",
               "rank" : "primary",
               "exchange" : "353",
               "carrier_only" : "true"
            }
         ]
      }
   ],
   "meta" : {
      "apiversion" : "1.0",
      "linkexpiration" : "2011-06-04",
      "searchlinks" : {
         "homepage" : {
            "linktext" : "Whitepages.com",
            "url" : "http://www.whitepages.com/16176/"
         },
         "self" : {
            "linktext" : "Link to this api call",
            "url" : "http://api.whitepages.com/reverse_phone/1.0/?phone=4103533986;api_key=729c1a751fd3d2428cfe2a7b43442c64;outputtype=JSON"
         }
      },
      "recordrange" : {
         "lastrecord" : "1",
         "firstrecord" : "1",
         "totalavailable" : 1
      },
      "searchid" : "03301350233067958544"
   },
   "result" : {
      "type" : "success",
      "code" : "Found Data",
      "message" : " "
   }
}';

$jsonresp = json_decode ($apiresponse, true); // Output is placed in an array

dump($jsonresp );

?>
