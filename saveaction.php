<?php
require 'header.php';

$action = $_REQUEST["action"];
$date = date('Y-m-d H:i:s');
$strQuery = "INSERT INTO stockaction (action,time,flag) VALUES(" . $action . ",'" . $date . "',0)";

$result = $mysql->query ( $strQuery );
if( $mysql->error != 0 )
{
	die( "Error:" . $mysql->errmsg() );
}
$mysql->close();
?>