<?php
header ( "Content-Type", "application/x-www-form-urlencoded; charset=utf8" );
require '../header.php';
$t = $_REQUEST['t'];
$d = $_REQUEST['d'];


if(empty($d))
	$sql = "SELECT distinct d.code FROM (SELECT distinct code,name,time,price,level FROM director WHERE (LEFT(code,2) = 'sh' or LEFT(code,2) = 'sz') and arrow REGEXP  '.1.1.1.1....' and flag = 0 ORDER by time desc ) d LEFT JOIN (SELECT code,current,100*(current-close)/close as rate FROM stockrecord WHERE time = (SELECT max(time) from stockrecord)) s on d.code = s.code where s.current is not null ORDER by d.time DESC, d.code ";
else
	$sql = "SELECT distinct d.code FROM (SELECT distinct code,name,time,price,level FROM director WHERE (LEFT(code,2) = 'sh' or LEFT(code,2) = 'sz') and arrow REGEXP  '.0.0.0.0....' and flag = 0 ORDER by time desc ) d LEFT JOIN (SELECT code,current,100*(current-close)/close as rate FROM stockrecord WHERE time = (SELECT max(time) from stockrecord)) s on d.code = s.code where s.current is not null ORDER by d.time DESC, d.code ";

$result = $mysql->query ( $sql );
$codes = array ();
while (!empty($result) && $mr = $result->fetch_array ( MYSQLI_ASSOC ) ) {
	$code = $mr['code'];
	if(substr($code,0,2)=='sh'){
		$code = '17:' . substr($code,2,6);
	}else if(substr($code,0,2)=='sz'){
		$code = '33:' . substr($code,2,6);
	}
	array_push ( $codes, $code );
}
echo join(",", $codes);
mysqli_free_result ( $result );

$mysql->close ();

?>