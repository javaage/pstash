<?php
require 'header.php';
require 'common.php';

$rt = date('Y-m-d H:i:s');
$ct = date('Y-m-d');
$mb = $ct . " 09:30:00";
$me = $ct . " 11:30:00";

$ab = $ct . " 13:00:00";
$as = $ct . " 14:57:00";
$ae = $ct . " 14:58:00";

if (!((time() >= strtotime($mb) && time() <= strtotime($me)) || (time() >= strtotime($ab) && time() <= strtotime($ae)))) {
	exit(0);
}
$code = $icode;

$time = time();
$ct = date('Y-m-d');
$deltaTime = $time - strtotime($ct);
$calTime = $time;
$baseUrl = "http://hq.sinajs.cn/list=";
$urlQuery = $baseUrl . $code;
$html = file_get_contents($urlQuery);
$html = str_replace("\"", "", $html);
$items = explode(',', $html);

$todayCurrent = $items[8];

$strQuery = "SELECT close,current, high, low, clmn FROM `indexrecord` WHERE code = '" . $code . "' and date < '" . $ct . "' and time - unix_timestamp(date) = $deltaTime ";

$result = $mysql -> query($strQuery);
$currentResult = array();
while ($mr = $result -> fetch_array(MYSQLI_ASSOC)) {
	array_push($currentResult, $mr);
}
$cnt = count($currentResult);

$totalClmn = 0;
for ($i = 0; $i < $cnt; $i++) {
	$totalClmn += $currentResult[$i]['clmn'];
}

if ($cnt > 0) {
	$clmnRate = $items[8] * $cnt / $totalClmn;
} else {
	$clmnRate = 1;
}

$strQuery = "SELECT close,high,low,clmn FROM `indexrecord` WHERE code = '" . $code . "' and date < '" . $ct . "' and time - unix_timestamp(date)= 53760 ";

$result = $mysql -> query($strQuery);
$endResult = array();
while ($mr = $result -> fetch_array(MYSQLI_ASSOC)) {
	array_push($endResult, $mr);
}
$totalClmn = 0;
$totalWidth = 0;
for ($i = 0; $i < count($endResult); $i++) {
	$totalClmn += $endResult[$i]['clmn'];
	$totalWidth += ($endResult[$i]['high'] - $endResult[$i]['low']) / $endResult[$i]['close'];
	$concept = $endResult[$i]['concept'];
}

$rateRange = $clmnRate * $totalWidth / count($endResult);
$prefBuy = $items[4] - $rateRange * $items[2];
$prefSell = $items[5] + $rateRange * $items[2];
$top = $items[2] * 1.1;
$bottom = $items[2] * 0.9;
$currentPrice = $items[3];
$high = $items[4];
$low = $items[5];
$prefBuy = $prefBuy > $bottom ? $prefBuy : $bottom;
$prefSell = $prefSell < $top ? $prefSell : $top;
$prefBuy = $prefBuy < $currentPrice ? $prefBuy : $currentPrice;
$prefSell = $prefSell > $currentPrice ? $prefSell : $currentPrice;
$mysql -> close();
echo "{\"prefBuy\":" . number_format($prefBuy, 2) . ",\"prefSell\":" . number_format($prefSell, 2) . ",\"current\":" . number_format($currentPrice, 2) . ",\"high\":" . number_format($high, 2) . ",\"low\":" . number_format($low, 2) . "}";

if($prefBuy>=$currentPrice){
	saveAction(3, "", "", $time, $time, 1, "推荐买入", "推荐买入", "", "");
}
if($prefSell<=$currentPrice){
	saveAction(1, "", "", $time, $time, 1, "推荐卖出", "推荐卖出", "", "");
}

function saveAction($action, $strWave, $strGw, $fTime, $lTime, $type, $content, $detail, $arrow, $pref) {
	global $kv, $mysql, $rt;

	$strQuery = "INSERT INTO stockaction (action,time,ftime,ltime,queue,gw,type,content,detail,arrow,pref) VALUES(" . $action . ",'" . $rt . "'," . $fTime . "," . $lTime . ",'" . $strWave . "','$strGw'," . $type . ",'$content','$detail','$arrow','$pref')";

	$mysql -> query($strQuery);
}
?>