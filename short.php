<?php
require 'header.php';
require 'common.php';
$o = $_REQUEST['o'];
switch ($o) {
	case 'ra':
		$order = 'order by a.rate, s.buy desc limit 30';
		break;
	case 'rd':
		$order = 'order by a.rate desc, s.buy desc limit 30';
		break;
	case 'sa':
		$order = 'order by s.buy, a.rate limit 30';
		break;
	case 'sd':
		$order = 'order by s.buy desc, a.rate limit 30';
		break;
	default:
		$order = 'order by gate desc, s.buy limit 30';
		break;
}
$sql = "SELECT s.code,a.name,s.buy as `signal`,'' as arrow,a.current,a.rate,a.current/a.avg as gate from (SELECT code,buy from sign where buy > 0) s INNER JOIN (SELECT code,name, current,avg,100*(current-close)/close as rate FROM stockrecord WHERE time = (SELECT max(time) from stockrecord)) a on s.code = a.code " . $order ;

$result = $mysql -> query($sql);
$codes = array();
while ($mr = $result -> fetch_array(MYSQLI_ASSOC)) {
	$code = strtolower($mr['code']);
	$gw = $kv -> get($code . 'gw');
	$mr['arrow'] = getArrow($gw);
	array_push($codes, $mr);
}
echo json_encode($codes, JSON_UNESCAPED_UNICODE);

mysqli_free_result($result);

$mysql -> close();
?>