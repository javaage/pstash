<?php
require '../header.php';
require '../common.php';
// 指定允许其他域名访问
header('Access-Control-Allow-Origin:*');
// 响应类型
header('Access-Control-Allow-Methods:POST');
// 响应头设置
header('Access-Control-Allow-Headers:x-requested-with,content-type');
$sql = "select code from holder";

$result = $mysql -> query($sql);
$codes = array();
while ($mr = $result -> fetch_array(MYSQLI_ASSOC)) {
	$codes[] = strtolower($mr['code']);
}

$url = "http://hq.sinajs.cn/list=" . join(",", $codes);

$time = strtotime("-12 minutes");

$sql = "select code,max(current) as large,min(current) as small from stockrecord where code in (select code from holder) and time > $time group by code";

$result = $mysql -> query($sql);
$contents = [];
while ($mr = $result -> fetch_array(MYSQLI_ASSOC)) {
	$contents[] = $mr;
}

$qian = array(" ", "　", "\t", "\n", "\r");
$hou = array("", "", "", "", "");
$url = str_replace($qian, $hou, $url);
$html = file_get_contents($url);

$stocks = explode(';', $html);
$e = [];

foreach ($stocks as $stock) {
	$stock = str_replace("\"", "", $stock);
	$shItems = explode(',', $stock);
	$names = explode('=', $shItems[0]);
	$names[1] = iconv('GB2312', 'utf-8//IGNORE', $names[1]);
	$code = substr($names[0], -8);
	$shCurrentPrice = $shItems[3];
	
	for($i = 0; $i < count($contents); $i++){//&& $shCurrentPrice > $contents[$i]['large']

		if($contents[$i]['code'] == $code && $shCurrentPrice/$contents[$i]['small'] > 1.02){
			$e[] = $code;
		}
	}
}
echo join(',', $e);
mysqli_free_result($result);

$mysql -> close();
?>