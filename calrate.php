<?php
require 'header.php';
$ct = date ( 'Y-m-d' );
$mb = $ct . " 09:30:00";
$me = $ct . " 11:30:00";

$ab = $ct . " 13:00:00";
$ae = $ct . " 15:00:00";
if (! ((time () >= strtotime ( $mb ) && time () <= strtotime ( $me )) || (time () >= strtotime ( $ab ) && time () <= strtotime ( $ae )))) {
	exit(0);
}

$isCal = memcache_get ( $mmc, 'calrate' );
if (empty ( $isCal ) || $isCal == 0) {
	exit(0);
} else {
	memcache_set ( $mmc, "calrate", 0, 0, 60 * 100 );
}

$rt = date ( 'Y-m-d H:i:s' );

$sql = "select preflist from candidate order by id desc limit 1";
$result = $mysql->query ( $sql );

if ($row = $result->fetch ()) {

	$candidates = preg_replace ( '/\s/', '', $row [0] );
	$codes = explode ( ',', $candidates );
	
	$ivalue = array ();
	
	for($i = 0; $i < count ( $codes ) - 1; $i ++) {
		$code = $codes [$i];
		
		$sqlmetric = "select time, current, (current-close)/close, name from stockrecord where code = '" . $code . "' and date = '" . $ct . "'";
		
		$rmetric = $mysql->query ( $sqlmetric );
		$metric = array ();
		while ( $mr = $rmetric->fetch () ) {
			array_push ( $metric, array (
					$mr [0],
					$mr [1],
					$mr [2],
					$mr [3] 
			) );
		}
		mysqli_free_result ( $rmetric );
		
		if (count ( $metric ) > 1) {
			$sx = 0;
			$sy = 0;
			$sxy = 0;
			$sx2 = 0;
			$sy2 = 0;
			$xavg = 0;
			$yavg = 0;
			$sxd = 0;
			$syd = 0;
			$sxd2 = 0;
			$syd2 = 0;
			for($j = 0; $j < count ( $metric ); $j ++) {
				$sx += $metric [$j] [0];
				$sy += $metric [$j] [1];
				$sxy += $metric [$j] [0] * $metric [$j] [1];
				$sx2 += $metric [$j] [0] * $metric [$j] [0];
				$sy2 += $metric [$j] [1] * $metric [$j] [1];
			}
			$xavg = $sx / count ( $metric );
			$yavg = $sy / count ( $metric );
			for($j = 0; $j < count ( $metric ); $j ++) {
				$sxd += ($metric [$j] [0] - $xavg);
				$syd += ($metric [$j] [1] - $yavg);
				$sxd2 += pow ( ($metric [$j] [0] - $xavg), 2 );
				$syd2 += pow ( ($metric [$j] [1] - $yavg), 2 );
			}
			
			$a = 10000 * (count ( $metric ) * $sxy - $sx * $sy) / (count ( $metric ) * $sx2 - $sx * $sx);
			$b = ($sx2 * $sy - $sx * $sxy) / (count ( $metric ) * $sx2 - $sx * $sx);
			$r = sqrt ( $sxd2 ) * sqrt ( $syd2 ) == 0 ? 1 : abs ( $sxd * $syd ) / (sqrt ( $sxd2 ) * sqrt ( $syd2 ));
			array_push ( $ivalue, "('" . $code . "', '" . $metric [count ( $metric ) - 1] [3] . "', " . $a . ", " . $b . ", " . $r . ", " . 100 * $metric [count ( $metric ) - 1] [2] . ", '" . $rt . "')" );
		}
	}
	
	mysqli_free_result ( $result );
	$sqlsave = "insert into cand_rate (code, name, a, b, r, increase, time) values " . join ( ",", $ivalue );
	
	$mysql->query ( $sqlsave );
}

$mysql->close ();
?>