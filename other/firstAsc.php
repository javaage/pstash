<?php
require '../header.php';
require '../common.php';
$strQuery = "select id, action, time, queue from stockaction where action > 1 order by time desc limit 1";

$result = $mysql->query ( $strQuery );
$waveList = array();
if($row = $result->fetch()){
	$date = $row[2];
	$queue = json_decode($row[3]);
	foreach ($queue as $wave){
		foreach ($wave->childWave as $sWave){
			if($sWave->asc==0)
				array_push($waveList, $sWave);
		}
	}
	if(count($waveList)>1){
		$strGetList = "select * from ";
	}
}else{
	echo "";
}


if( $mysql->error != 0 )
{
	die( "Error:" . $mysql->errmsg() );
}
$mysql->close();
?>