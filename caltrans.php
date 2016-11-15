<?php 
require 'header.php';
$ct = date ( 'Y-m-d' );
$mb = $ct . " 09:30:00";
$me = $ct . " 11:30:00";

$ab = $ct . " 13:00:00";
$ae = $ct . " 15:00:00";
if (! ((time () >= strtotime ( $mb ) && time () <= strtotime ( $me )) || (time () >= strtotime ( $ab ) && time () <= strtotime ( $ae )))) {
	exit(0);
}

$strTrans = memcache_get ( $mmc, 'caltrans' );

if (empty ( $strTrans )) {
	exit(0);
}

$flTime = json_decode($strTrans);

$fTime = floor($flTime->firstT/60) * 60;
$lTime = floor($flTime->lastT/60) * 60;

if($fTime >= $lTime)
	exit(0);
$rt = date ( 'Y-m-d H:i:s' );

$sql = "INSERT cand_trans (code, name,increase,trans,time) SELECT r.code,r.name,r.increase,r.trans,now() from (select f.code as code,f.name as name,100 * (l.current - l.close)/l.close as increase,100 * (l.current - f.current)/f.close as trans from (SELECT code,name,close,current FROM `stockrecord` WHERE date='" . $ct . "' AND time >= " . $fTime . " and time < " . ($fTime + 60) . ") f LEFT JOIN (SELECT code,name,close,current FROM stockrecord WHERE date='" . $ct . "' AND time >= " . $lTime . " AND time < " . ($lTime+60) . ") l ON f.code = l.code) r ";
echo $sql;
$mysql->query ( $sql );

$mysql->close ();
memcache_set ( $mmc, "caltrans", "", 0, 60 * 100 );
?>