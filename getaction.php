<?php
class Direct {
	var $action=0;
	var $ftime=0;
	var $ltime=0;
	var $time="";
	var $type=0;
	var $level=0;
	var $asc=0;
	var $wave;
	var $count=0;
	var $content="";
	var $detail="";
	var $arrow="";
}
require 'header.php';
$lm = date ( "Y-m-d H:i:s", strtotime ( "-5 minute" ) );

$strQuery = "select action, ftime, ltime, time, type, content, detail, arrow from stockaction where time > '$lm'  order by id desc limit 1";

$result = $mysql->query ( $strQuery );

$lstAction = array ();

if ( !empty($result) && $mr = $result->fetch_array ( MYSQLI_ASSOC ) ) {
	echo json_encode($mr);
}

exit(0);

if (count ( $lstAction ) == 2) {
	$na = $lstAction [0];
	$oa = $lstAction [1];
	$d = new Direct ();
	$d->action = $na ["action"];
	$d->ftime = $na ["ftime"];
	$d->ltime = $na ["ltime"];
	$d->time = $na ["time"];
	$d->type = $na ["type"];
	$d->arrow = $na ["arrow"];
	$nw = json_decode ( $na ["gw"] );
	$ow = json_decode ( $oa ["gw"] );
	
	if ($nw->level != $ow->level || $nw->asc != $ow->asc) {
		$d->level = $nw->level;
		$d->asc = 1 - $nw->asc;
	} else {
		while ( count ( $nw->childWave ) == count ( $ow->childWave ) && count ( $nw->childWave ) > 0) {
			$parent = $nw;
			$nw = $nw->childWave [count ( $nw->childWave ) - 1];
			$ow = $ow->childWave [count ( $ow->childWave ) - 1];
		}

		$d->level = $nw->level;
		$d->asc = 1 - $nw->asc;
		
	}
	
	$n = count($nw->childWave);
	$curDir = 1 - $nw->childWave[$n-1]->asc;
	$d->content = $tls [$d->level] . ($n + 1) . "浪" . $dir [$nw->asc];
	if(empty($parent)){
		$d->detail = $tls [$d->level - 1] . $dir [$curDir] . "，区间" . number_format($nw->low, 0) . "-" . number_format($nw->high, 0) . $todo[$curDir][min(7,$nw->level)];
	}else{
		$d->detail = $tls [$d->level - 1] . $dir [$curDir] . "，区间" . number_format($parent->low, 0) . "-" . number_format($parent->high, 0) . $todo[$curDir][min(7,$parent->level)];
	}
	echo json_encode ( $d );
}

if ($mysql->error != 0) {
	die ( "Error:" . $mysql->errmsg () );
}
$mysql->close ();
?>