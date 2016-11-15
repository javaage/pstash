<?php
header ( "Content-Type", "application/x-www-form-urlencoded; charset=utf8" );
require 'header.php';
$t = $_REQUEST['t'];

$sql = "SELECT action,time,content,detail,arrow,pref,strong FROM `stockaction` ORDER by time desc limit 60";

$result = $mysql->query ( $sql );
$codes = array ();
while (!empty($result) && $mr = $result->fetch_array ( MYSQLI_ASSOC ) ) {
	array_push ( $codes, $mr );
}
echo json_encode ( $codes, JSON_UNESCAPED_UNICODE );

mysqli_free_result ( $result );

$mysql->close ();

?>