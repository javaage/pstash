<?php
require 'header.php';
require 'common.php';
$rt = date('Y-m-d H:i:s');
$ct = date('Y-m-d');
$mb = $ct . " 09:30:00";
$me = $ct . " 11:30:00";

$ab = $ct . " 13:00:00";
$as = $ct . " 14:58:00";
$ae = $ct . " 14:59:00";

if (!((time() >= strtotime($mb) && time() <= strtotime($me)) || (time() >= strtotime($ab) && time() <= strtotime($ae)))) {
	exit(0);
}
$sqlUpdate = "update sign s INNER JOIN (SELECT code,sum(money) / sum(clmn) as avg FROM stockrecord WHERE time in (SELECT max(time) from stockrecord group by date ) group by code ) m on s.code = m.code set s.avg = m.avg WHERE m.avg > 0";
$mysql -> query($sqlUpdate);

$sql = "SELECT code FROM sign";
$result = $mysql -> query($sql);
$codes = array();
while ($mr = $result -> fetch_array(MYSQLI_ASSOC)) {
	$code = strtolower($mr['code']);
	$codes[] = $code;
}
mysqli_free_result($result);
foreach ($codes as $code) {
	$pref = prefPrice($code);
	$update = "UPDATE sign SET prefBuy=$pref->prefBuy,prefSell=$pref->prefSell,current=$pref->current,high=$pref->high,low=$pref->low,concept='$pref->concept' WHERE code='$code'";

	$mysql -> query($update);
}

$sqlClear = "DELETE FROM indexrecord WHERE id in (select id from (SELECT max(id) as id FROM `indexrecord` group by code, time HAVING count(time) > 1) s)";
$mysql -> query($sqlClear);

?>