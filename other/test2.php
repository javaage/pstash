<?php
require '../header.php';
require '../common.php';

$urlQueryIndex = "http://hq.sinajs.cn/list=sz399001,sh000001,sz399006";

$rt = date('Y-m-d H:i:s');
$html = file_get_contents($urlQueryIndex);
$html = str_replace("\"", "", $html);
$stocks = explode(';', $html);

$sqlInsert = array();
for ($i = 0; $i < count($stocks) - 1; $i++) {
	$shItems = explode(',', $stocks[$i]);

	$shCurrentPrice = $shItems[3];
	$shClosePrice = $shItems[2];
	$openPrice = $shItems[1];
	$time = strtotime(substr($shItems[31], 0, strlen($shItems[31]) - 2) . '00');
	$names = explode('=', $shItems[0]);
	$names[1] = iconv('GB2312', 'utf-8//IGNORE', $names[1]);
	$code = substr($names[0], -8);
	echo $shItems[8] . "  ";
	$sqlInsert[] = "('" . $code . "' , '" . $shItems[30] . "' , '" . $time . "' , '" . $names[1] . "' , '" . $shItems[2] . "' , '" . $shItems[1] . "' , '" . $shItems[3] . "' , '" . $shItems[4] . "' , '" . $shItems[5] . "' , '" . $shItems[8] . "' , '" . $shItems[9] . "' , '" . $shItems[9] / $shItems[8] . "' )";
}
$sql = "INSERT INTO indexrecord (code, date, time, name, close, open, current, high, low, clmn, money, avg) VALUES  " . join(",", $sqlInsert);
$mysql -> query($sql);
$mysql -> close();