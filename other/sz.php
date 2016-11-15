<?php
require '../header.php';
require '../common.php';
$n = $_REQUEST["n"];
if(empty($n) || $n < 1){
	$n = 238;
}
$sql = "select r.dex,convert(r.strong,decimal(6,2)) as strong,r.time from (select sh.current as dex,(sz.current -sh.current + (SELECT avg(current) from indexrecord where code='sh000001' order by id desc limit $n)) as strong,sz.time from (SELECT code,current * 0.287 as current,time FROM indexrecord WHERE code='sz399001') sz inner join (SELECT code,current,time FROM indexrecord WHERE code='sh000001') sh on sz.time = sh.time ORDER by time desc LIMIT $n) r order by r.time ";

$result = $mysql -> query($sql);
$strongs = array();
while ($mr = $result -> fetch_array(MYSQLI_ASSOC)) {
	$mr['time'] = date('H:i',$mr['time']);
	$strongs[] = $mr;
}
echo json_encode($strongs);
mysqli_free_result($result);

$mysql -> close();
?>