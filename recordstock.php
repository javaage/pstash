<?php
header("Content-Type: text/html; charset=utf8");
require 'header.php';
require 'common.php';

$ct = date('Y-m-d');

$mb = $ct . " 09:30:00";
$me = $ct . " 11:30:00";
$ab = $ct . " 13:00:00";
$ae = $ct . " 15:01:00";

if (!((time() >= strtotime($mb) && time() <= strtotime($me)) || (time() >= strtotime($ab) && time() <= strtotime($ae)))) {
	exit(0);
}

$urlIndex = "http://hq.sinajs.cn/list=$icode";

$htmlIndex = file_get_contents($urlIndex);
$htmlIndex = str_replace("\"", "", $htmlIndex);
$items = explode(',', $htmlIndex);
$names = explode('=', $items[0]);
$names[1] = iconv('GB2312', 'utf-8//IGNORE', $names[1]);
$code = substr($names[0], -8);
if ($items[30] != $ct) {
	exit(0);
}
$time = strtotime(date('Y-m-d H:i'));

if (count($items) >= 33 && $items[1] > 0 && $items[2] > 0 && $items[3] > 0 && $items[4] > 0 && $items[5] > 0 && $items[8] > 0 && $items[9] > 0) {
	$sql = "INSERT INTO indexrecord (code, date, time, name, close, open, current, high, low, clmn, money, avg) VALUES ('" . $code . "' , '" . $items[30] . "' , '" . $time . "' , '" . $names[1] . "' , '" . $items[2] . "' , '" . $items[1] . "' , '" . $items[3] . "' , '" . $items[4] . "' , '" . $items[5] . "' , '" . $items[8] . "' , '" . $items[9] . "' , '" . $items[9] / $items[8] . "' ) ";

	$mysql -> query($sql);
	if ($mysql -> error != 0) {
		die("Error:" . $mysql -> errmsg());
	}
}

$sqlInserts = array();
$urls = getUrl();
for ($i = 0; $i < count($urls); $i++) {
	$qian = array(" ", "��", "\t", "\n", "\r");
	$hou = array("", "", "", "", "");
	$url = str_replace($qian, $hou, $urls[$i]);
	$html = file_get_contents($url);
	$stocks = explode(';', $html);

	foreach ($stocks as $stock) {
		$stock = str_replace("\"", "", $stock);
		$items = explode(',', $stock);
		if (count($items) < 33 || $items[1] <= 0 || $items[2] <= 0 || $items[3] <= 0 || $items[4] <= 0 || $items[5] <= 0 || $items[6] <= 0) {
			continue;
		}
		$names = explode('=', $items[0]);
		$names[1] = iconv('GB2312', 'utf-8//IGNORE', $names[1]);
		$code = substr($names[0], -8);
		array_push($sqlInserts, " ('" . $code . "' , '" . $items[30] . "' , '" . $time . "' , '" . $names[1] . "' , '" . $items[2] . "' , '" . $items[1] . "' , '" . $items[3] . "' , '" . $items[4] . "' , '" . $items[5] . "' , '" . $items[8] . "' , '" . $items[9] . "' , '" . $items[9] / $items[8] . "' ) ");
	}
}

$sql = "INSERT INTO stockrecord (code, date, time, name, close, open, current, high, low, clmn, money, avg) VALUES " . join(",", $sqlInserts);

$mysql -> query($sql);
if ($mysql -> error != 0) {
	die("Error:" . $mysql -> errmsg());
}
$mysql -> close();
?>