<?php
require 'header.php';
require 'common.php';
$n = $_REQUEST["n"];
$gw = $kv -> get("g");

$l2 = $gw;
while ($l2 -> level > $n + 1) {
	$l2 = $l2 -> childWave[count($l2 -> childWave) - 1];
}
$cl2 = count($l2 -> childWave);
$asc = $l2 -> childWave[0] -> asc;
$indexs = array();

for ($i = 0; $i < $cl2; $i++) {
	if ($asc) {
		if ($i % 2 == 0) {
			$indexs[] = array($l2 -> childWave[$i] -> low, date("m/d H:i", $l2 -> childWave[$i] -> beginTime));
		} else {
			$indexs[] = array($l2 -> childWave[$i] -> high, date("m/d H:i", $l2 -> childWave[$i] -> beginTime));
		}
	} else {
		if ($i % 2 == 0) {
			$indexs[] = array($l2 -> childWave[$i] -> high, date("m/d H:i", $l2 -> childWave[$i] -> beginTime));
		} else {
			$indexs[] = array($l2 -> childWave[$i] -> low, date("m/d H:i", $l2 -> childWave[$i] -> beginTime));
		}
	}
}

$reals = $indexs;
$last = $l2 -> childWave[$cl2 - 1];
if ($last -> asc) {
	$reals[] = array($last -> high, date("m/d H:i", $last -> endTime));
} else {
	$reals[] = array($last -> low, date("m/d H:i", $last -> endTime));
}
$cals = calNext($indexs);
$cals = calNext($cals);
$r = new stdClass();
$r -> reals = $reals;
$r -> cals = $cals;
echo json_encode($r);
exit(0);

function calNext($a) {
	if (count($a) < 3) {
		return $a;
	} else {
		if (count($a) % 2 == 0) {
			$b = 1;
		} else {
			$b = 0;
		}
		$gc = floor((count($a) - $b) / 2);

		$delta = 0;
		for ($i = 0; $i < $gc; $i++) {
			$delta += ($a[2 * $i + 1 + $b][0] - $a[2 * $i + $b][0]);

		}
		$delta /= $gc;

		$a[] = array($a[count($a) - 1][0] + $delta, "");
		return $a;
	}
}
