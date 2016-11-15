<?php
require 'header.php';
$ftime = $_REQUEST['ftime'];
$ltime = $_REQUEST['ltime'];
$asc = $_REQUEST['asc'];
$asc = 1;
$ftime = 1449556020;
$ltime = 1449557340;
if ($asc) {
	$strUpdate = "update attend set `signal` = 0 where code in (select f.code  from (SELECT code,name,close,current FROM `stockrecord` WHERE time >= " . $ftime . " and time < " . ($ftime + 60) . ") f LEFT JOIN (SELECT code,name,close,current FROM stockrecord WHERE time >= " . ($ltime - 60) . " AND time < " . $ltime . ") l ON f.code = l.code where f.current > l.current)";
	echo $strUpdate . "</br>";
	$mysql -> query($strUpdate);
	$strUpdate = "update holder set `signal` = `signal`  + 1 where code in (select f.code  from (SELECT code,name,close,current FROM `stockrecord` WHERE time >= " . $ftime . " and time < " . ($ftime + 60) . ") f LEFT JOIN (SELECT code,name,close,current FROM stockrecord WHERE time >= " . ($ltime - 60) . " AND time < " . $ltime . ") l ON f.code = l.code where f.current > l.current)";
	echo $strUpdate . "</br>";
	$mysql -> query($strUpdate);
} else {
	$strUpdate = "update attend set `signal` = `signal`  + 1 where code in (select f.code  from (SELECT code,name,close,current FROM `stockrecord` WHERE time >= " . $ftime . " and time < " . ($ftime + 60) . ") f LEFT JOIN (SELECT code,name,close,current FROM stockrecord WHERE time >= " . ($ltime - 60) . " AND time < " . $ltime . ") l ON f.code = l.code where f.current < l.current)";
	echo $strUpdate . "</br>";
	$mysql -> query($strUpdate);
	$strUpdate = "update holder set `signal` = 0 where code in (select f.code  from (SELECT code,name,close,current FROM `stockrecord` WHERE time >= " . $ftime . " and time < " . ($ftime + 60) . ") f LEFT JOIN (SELECT code,name,close,current FROM stockrecord WHERE time >= " . ($ltime - 60) . " AND time < " . $ltime . ") l ON f.code = l.code where f.current < l.current)";
	echo $strUpdate . "</br>";
	$mysql -> query($strUpdate);
}
if ($mysql -> error != 0) {
	die("Error:" . $mysql -> errmsg());
}
$mysql -> close();
?>