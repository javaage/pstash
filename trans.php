<?php
header("Content-Type", "application/x-www-form-urlencoded; charset=utf8");
require 'header.php';
$t = $_REQUEST['t'];
$urlIndex = "http://hq.sinajs.cn/list=$icode";
$baseUrl = "http://hq.sinajs.cn/list=";
$htmlIndex = file_get_contents($urlIndex);
$htmlIndex = str_replace("\"", "", $htmlIndex);
$items = explode(',', $htmlIndex);
$indexRate = ($items[3] - $items[2]) / $items[2] * 100 + 4;

$sqlIndex = "select distinct close, current, date, time from indexrecord where code = '$icode' order by id desc limit 1";

$tResult = $mysql -> query($sqlIndex);

$row = mysqli_fetch_assoc($tResult);
if (isset($row)) {
	if ($_GET['time']) {
		$time = $_GET['time'];
	} else {
		$time = $row["time"];
	}

	$date = $row["date"];
	
	if (empty($t))
		$sql = "SELECT distinct d.code,d.name,s.trans,s.increase,s.current,d.time,d.level FROM (SELECT distinct code,name,time,price,level FROM director WHERE (LEFT(code,2) = 'sh' or LEFT(code,2) = 'sz') and arrow REGEXP  '.1.1.1.1....' and flag = 0) d inner JOIN (SELECT code,trans,increase,current from cand_trans where time=(SELECT MAX(time) from cand_trans)) s on d.code = s.code where s.increase is not null ORDER by s.trans desc ";
	else
		$sql = "SELECT distinct d.code,d.name,s.trans,s.increase,s.current,d.time,d.level FROM (SELECT distinct code,name,time,price,level FROM director WHERE (LEFT(code,2) = 'sh' or LEFT(code,2) = 'sz') and arrow REGEXP  '.1.1.1.1....' and flag = 0 and time <= '$t') d inner JOIN (SELECT code,trans,increase,current from cand_trans where time = '$t') s on d.code = s.code where s.increase is not null ORDER by s.trans desc ";
	
	$result = $mysql -> query($sql);
	$codes = array();
	while ($mr = $result -> fetch_array(MYSQLI_ASSOC)) {
		array_push($codes, $mr);
	}
	echo json_encode($codes, JSON_UNESCAPED_UNICODE);

	mysqli_free_result($result);
}

$mysql -> close();
?>