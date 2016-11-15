<?php 
header ( "Content-Type: text/html; charset=utf8" );
require 'header.php';
$ct = date ( 'Y-m-d' );
$mb = $ct . " 09:30:00";
$me = $ct . " 11:30:00";

$ab = $ct . " 13:00:00";
$ae = $ct . " 15:01:00";

if (! ((time () >= strtotime ( $mb ) && time () <= strtotime ( $me )) || (time () >= strtotime ( $ab ) && time () <= strtotime ( $ae )))) {
	exit ( 0 );
}
$urlswhy = "http://vip.stock.finance.sina.com.cn/q/view/SwHy.php";
$urlbkstock = "http://vip.stock.finance.sina.com.cn/quotes_service/api/json_v2.php/Market_Center.getHQNodeData?page=1&num=50&sort=symbol&asc=1&node=sw_cm&symbol=&_s_r_a=init";
$type = "swhy";

$htmlswbk = file_get_contents ( $urlswhy );

$cswbk = explode("=", $htmlswbk);
$qian = array (
		" ",
		"��",
		"\t",
		"\n",
		"\r"
);
$hou = array (
		"",
		"",
		"",
		"",
		""
);
$keyqian = array(
		'symbol',
		'code',
		'name',
		'trade',
		'pricechange',
		',per',
		'changepercent',
		'buy',
		'sell',
		'settlement',
		'open',
		'high',
		'low',
		'volume',
		'amount',
		'ticktime',
		'pb',
		'mktcap',
		'nmc',
		'turnoverratio'
);
$keyhou = array(
		'"symbol"',
		'"code"',
		'"name"',
		'"trade"',
		'"pricechange"',
		',"per"',
		'"changepercent"',
		'"buy"',
		'"sell"',
		'"settlement"',
		'"open"',
		'"high"',
		'"low"',
		'"volume"',
		'"amount"',
		'"ticktime"',
		'"pb"',
		'"mktcap"',
		'"nmc"',
		'"turnoverratio"'
);
$strbk = str_replace ( $qian, $hou, $cswbk[1] );
$strbk = iconv('GB2312', 'utf-8//IGNORE', $strbk);
$swbk = json_decode($strbk);
$time = strtotime(date('Y-m-d H:i'));
foreach ($swbk as $bk){
	$items = explode(",", $bk);
	$code = $items[0];
	$name = $items[1];
	$increase = $items[5];
	
	$sql = 'INSERT INTO bkrecord (code, name, increase,date,time) VALUES ("' . $code . '","' . $name . '",' . $increase . ',"' . $ct . '",' . $time . ')';

	$mysql->query ( $sql );
}

if ($mysql->error != 0) {
	die ( "Error:" . $mysql->errmsg () );
}
$mysql->close ();
?>