<?php
require 'header.php';
$type = $_REQUEST['type'];

$lm = date("Y-m-d H:i:s", strtotime("-5 minute"));

$strQuery = "SELECT time FROM `stockaction` where type = 0 ORDER by time desc limit 10 ";

$result = $mysql->query ( $strQuery );
$history = array();
while($row = $result->fetch()){
	array_push($history, $row[0]);
}
echo json_encode($history);
if( $mysql->error != 0 )
{
	die( "Error:" . $mysql->errmsg() );
}
$mysql->close();
?>