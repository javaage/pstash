<?php
header ( "Content-Type", "application/x-www-form-urlencoded; charset=utf8" );
require 'header.php';
$t = $_REQUEST['t'];
$d = $_REQUEST['d'];
if(empty($d))
	$sql = "SELECT distinct d.code,d.name,d.price,s.current,s.rate,d.time,d.level FROM (SELECT distinct code,name,time,price,level FROM director WHERE (LEFT(code,2) = 'sh' or LEFT(code,2) = 'sz') and arrow REGEXP  '.1.1.1.1....' and flag = 0 ORDER by time desc ) d LEFT JOIN (SELECT code,current,100*(current-close)/close as rate FROM stockrecord WHERE time = (SELECT max(time) from stockrecord)) s on d.code = s.code where s.current is not null ORDER by d.time DESC, d.code ";
else
	$sql = "SELECT distinct d.code,d.name,d.price,s.current,s.rate,d.time,d.level FROM (SELECT distinct code,name,time,price,level FROM director WHERE (LEFT(code,2) = 'sh' or LEFT(code,2) = 'sz') and arrow REGEXP  '.0.0.0.0....' and flag = 0 ORDER by time desc ) d LEFT JOIN (SELECT code,current,100*(current-close)/close as rate FROM stockrecord WHERE time = (SELECT max(time) from stockrecord)) s on d.code = s.code where s.current is not null ORDER by d.time DESC, d.code ";

$result = $mysql->query ( $sql );
$codes = array ();
while (!empty($result) && $mr = $result->fetch_array ( MYSQLI_ASSOC ) ) {
	array_push ( $codes, $mr );
}
echo json_encode ( $codes, JSON_UNESCAPED_UNICODE );

mysqli_free_result ( $result );

$mysql->close ();

?>