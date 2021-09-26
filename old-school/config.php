<?php

$xc['chanel_id'] = '';
$xc['bot_token'] = '';
$xc['webhook_url'] = '';

$xc['db_host'] = 'localhost';
$xc['db_name'] = '';
$xc['db_user'] = '';
$xc['db_pass'] = '';

$xc['db'] = mysqli_connect ($xc['db_host'],$xc['db_user'],$xc['db_pass']);
mysqli_select_db($xc['db'],$xc['db_name']);
mysqli_query($xc['db'],'SET NAMES utf8');          
mysqli_query($xc['db'],'SET CHARACTER SET utf8');  
mysqli_query($xc['db'],'SET COLLATION_CONNECTION="utf8_general_ci"');

function db_query($sql,$type='s') {
    
    global $xc;
    
    $a = mysqli_query($xc['db'],$sql);
    
    switch($type) {

         case 's':
         
         if ( @mysqli_num_rows($a) > 0 ) {
            $result = array();
                while ($b = mysqli_fetch_assoc($a)) {
                    $result[] = $b;
                }
         }
         
         else
           $result = false;
         
         break;

         case 'u':
         if (mysqli_affected_rows($xc['db']) > 0) $result = true;
         else $result = mysqli_error($xc['db']);
         break;
         
         case 'i':
         if ($r = mysqli_insert_id($xc['db'])) $result = $r;
         else $result = mysqli_error($xc['db']);
         break;
         
         case 'd':
         if (mysqli_affected_rows($xc['db']) > 0) $result = true;
         else $result = mysqli_error($xc['db']);
         break;
    }
    
    return $result;
}