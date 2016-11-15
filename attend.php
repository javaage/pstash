<?php
require 'header.php';
require 'common.php';
$a = $_REQUEST['a'];

if ($a == 'd') {
	$code = $_REQUEST['c'];
	$sql = "DELETE FROM attend WHERE code = '$code'";
	$mysql -> query($sql);
} else if ($a == 'a') {
	$code = $_REQUEST['c'];
	$name = $_REQUEST['n'];
	$sql = "INSERT INTO attend (code, name, time) VALUES ('$code','$name',now())";
	$mysql -> query($sql);
} else {
	$sql = "SELECT a.code,a.name,s.buy as `signal`, '' as arrow,r.current,r.rate,r.current/r.avg as gate from attend a INNER JOIN sign s on a.code = s.code inner join (SELECT code,name, current,avg,100*(current-close)/close as rate FROM stockrecord WHERE time = (SELECT max(time) from stockrecord)) r on a.code = r.code order by s.buy desc";

	$result = $mysql -> query($sql);
	$codes = array();
	while ($mr = $result -> fetch_array(MYSQLI_ASSOC)) {
		$code = strtolower($mr['code']);
		$gw = $kv->get($code . 'gw');

		$mr['arrow'] = getArrow($gw);
		array_push($codes, $mr);
	}
	echo json_encode($codes, JSON_UNESCAPED_UNICODE);
	
	mysqli_free_result($result);	
}

$mysql -> close();
?>