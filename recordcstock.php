<?php
header("Content-Type: text/html; charset=utf8");
require 'header.php';
require 'common.php';
$rt = date('Y-m-d H:i:s');
$ct = date('Y-m-d');
$mb = $ct . " 09:30:00";
$me = $ct . " 11:30:00";

$ab = $ct . " 13:00:00";
$as = $ct . " 14:57:00";
$ae = $ct . " 14:58:00";

if (!((time() >= strtotime($mb) && time() <= strtotime($me)) || (time() >= strtotime($ab) && time() <= strtotime($ae)))) {
	exit(0);
}
$urlswhy = "http://money.finance.sina.com.cn/q/view/newFLJK.php?param=class";

$htmlswbk = file_get_contents($urlswhy);

$cswbk = explode("=", $htmlswbk);
$qian = array(" ", "��", "\t", "\n", "\r");
$hou = array("", "", "", "", "");

$strbk = str_replace($qian, $hou, $cswbk[1]);
$strbk = iconv("gb2312", "utf-8//IGNORE", $strbk);
$swbk = json_decode($strbk);
$time = strtotime(date('Y-m-d H:i'));
$sqlInserts = array();
$sqlSaves = array();
foreach ($swbk as $bk) {
	$items = explode(",", $bk);
	$code = $items[0];
	$name = $items[1];
	$increase = $items[5];

	$ascCount = $dscCount = 0;
	$sWave = new Wave();
	$sWave -> asc = 1;
	$sWave -> high = 0;
	$sWave -> low = 0;
	$sWave -> beginTime = $sWave -> endTime = strtotime('09:31:00');
	$sWave -> id = uniqid();
	$sWave -> level = 1;
	$gw = null;

	if ($kv -> get($code . "sWave")) {
		$ascCount = $kv -> get($code . "ascCount");
		$dscCount = $kv -> get($code . "dscCount");
		$sWave = $kv -> get($code . "sWave");
		$gw = $kv -> get($code . "gw");
	} else {
		$sql = "select wv,gw from waverecord where code = '$code' order by dt desc limit 1";
		$result = $mysql -> query($sql);
		if (!empty($result) && $row = $result -> fetch()) {
			$strWave = $row[0];
			$strgw = $row[1];

			if (!empty($strWave)) {
				$sWave = json_decode($strWave);
				$kv -> set($code . "sWave", $sWave);
			}
			if (!empty($strgw)) {
				$gw = json_decode($strgw);
				$kv -> set($code . "gw", $gw);
			}
		}
	}

//	if (empty($gw) || empty($sWave)) {
//
//		$mail = new SaeMail();
//		$ret = $mail -> quickSend('hb_java@sina.com', 'ichessϵͳ����', $code, 'hb_java@sina.com', 'Tangyc_123');
//
//		// ����ʧ��ʱ���������ʹ�����Ϣ
//		if ($ret === false)
//			var_dump($mail -> errno(), $mail -> errmsg());
//	}

	if (time() > strtotime($as) && time() <= strtotime($ae)) {
		array_push($sqlSaves, "('$code','" . $ct . "','" . json_encode($sWave) . "','" . json_encode($gw) . "')");
		continue;
	}
	$shCurrentPrice = $items[3];
	$shClosePrice = $items[3];
	$openPrice = $items[3];
	// ��ʼ��
	if ($sWave -> high == 0) {
		if ($openPrice > $shClosePrice) {
			$sWave -> asc = 1;
			$sWave -> high = $openPrice;
			$sWave -> low = $shClosePrice;
		} else {
			$sWave -> asc = 0;
			$sWave -> low = $openPrice;
			$sWave -> high = $shClosePrice;
		}
		$sWave -> beginTime = $sWave -> endTime = $time;
	}

	// ���ɼ�������
	if ($sWave -> asc) {
		if ($shCurrentPrice >= $sWave -> high) {
			$sWave -> high = $shCurrentPrice;
			$sWave -> endTime = $time;
			$ascCount = 0;
			$dscCount = 0;
		} else {
			$dscCount++;
			$ascCount--;
			if ($dscCount >= 2) {
				$cloneWave = json_decode(json_encode($sWave));
				dealWave($gw, $sWave, $code, $name, $shCurrentPrice);

				$sWave = new Wave();
				$sWave -> asc = 0;
				$sWave -> high = $cloneWave -> high;
				$sWave -> beginTime = $cloneWave -> endTime;
				$sWave -> low = $shCurrentPrice;
				$sWave -> endTime = $time;
				$sWave -> id = uniqid();
				$sWave -> level = 1;
			}
		}
	} else {// �ɼ�����������
		if ($shCurrentPrice <= $sWave -> low) {
			$sWave -> low = $shCurrentPrice;
			$sWave -> endTime = $time;
			$ascCount = 0;
			$dscCount = 0;
		} else {
			$dscCount--;
			$ascCount++;
			if ($ascCount >= 2) {
				$cloneWave = json_decode(json_encode($sWave));
				dealWave($gw, $sWave, $code, $name, $shCurrentPrice);
				$sWave = new Wave();
				$sWave -> asc = 1;
				$sWave -> high = $shCurrentPrice;
				$sWave -> endTime = $time;
				$sWave -> low = $cloneWave -> low;
				$sWave -> beginTime = $cloneWave -> endTime;
				$sWave -> id = uniqid();
				$sWave -> level = 1;
			}
		}
	}

	$kv -> set($code . "ascCount", $ascCount);
	$kv -> set($code . "dscCount", $dscCount);
	$kv -> set($code . "sWave", $sWave);
	$kv -> set($code . "gw", $gw);

	array_push($sqlInserts, ' ("' . $code . '","' . $name . '",' . $increase . ',"' . $ct . '",' . $time . ')');
}
if (count($sqlSaves) > 0) {
	$sql = "INSERT INTO waverecord (code,dt,wv,gw) VALUES " . join(",", $sqlSaves);
	$mysql -> query($sql);
} else {
	$sql = "INSERT INTO crecord (code, name, increase,date,time) VALUES " . join(",", $sqlInserts);
	$mysql -> query($sql);
}
if ($mysql -> error != 0) {
	die("Error:" . $mysql -> errmsg());
}
$mysql -> close();

function saveDirector($code, $name, $price, $type, $level,$total,$arrow) {
	global $kv, $mysql, $rt;
	$strQuery = "INSERT INTO director(code, name, time, price, type, level,total,arrow) VALUES ('$code','$name','$rt',$price,$type,$level,$total,'$arrow')";

	$mysql -> query($strQuery);
}

function dealWave(&$gw, $w, $code, $name, $price) {
	global $mysql;
	$ow = json_decode(json_encode($gw));
	saveWave($gw, $w);
	$nw = $gw;
	if ($nw -> level == $ow -> level && $nw -> asc == $ow -> asc) {
		while (count($nw -> childWave) == count($ow -> childWave) && $nw -> count == $ow -> count) {
			$nw = $nw -> childWave[count($nw -> childWave) - 1];
			$ow = $ow -> childWave[count($ow -> childWave) - 1];
		}
	}
	$n = count($nw -> childWave);
	$lchild = $nw -> childWave[$n - 1];
	$brother = $nw -> childWave[$n - 2];
	$total = countArrow($gw);
	$arrow = getArrow($gw);
	if ($nw -> level > 5 && $nw -> asc && $lchild -> asc) {// ���Է����������ָ��
		$strQuery = "UPDATE director SET flag = 1 where code = '$code' and flag = 0";
		$mysql -> query($strQuery);
		saveDirector($code, $name, $price, 1, $nw -> level,$total,$arrow);
	}
	if ($nw -> level > 5 && !$lchild -> asc) {// ���Է�����������ָ��
		$strQuery = "UPDATE director SET flag = 1 where code = '$code' and flag = 1";
		$mysql -> query($strQuery);
		saveDirector($code, $name, $price, 0, $nw -> level,$total,$arrow);
	}
}

function saveHistory(&$node) {
	global $mysql;
	if (empty($node))
		return;
	for ($i = 0; $i < count($node -> childWave); $i++) {
		$cw = $node -> childWave[$i];
		if (!empty($cw -> childWave)) {
			$cw -> count = count($cw -> childWave);
			$cw -> childWave = array();
		}
	}
}

function saveGlobal($g) {

}
?>