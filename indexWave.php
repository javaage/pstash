<?php
require 'header.php';
require 'common.php';

$rt = date('Y-m-d H:i:s');
$ct = date('Y-m-d');
$mb = $ct . " 09:30:00";
$me = $ct . " 11:30:00";

$ab = $ct . " 13:00:00";
$as = $ct . " 14:58:00";
$ae = $ct . " 14:59:00";

if (!((time() >= strtotime($mb) && time() <= strtotime($me)) || (time() >= strtotime($ab) && time() <= strtotime($ae)))) {
	exit(0);
}
$sqlInserts = array();
$sqlSaves = array();
$urls = getUrl();
for ($i = 0; $i < count($urls); $i++) {
	$qian = array(" ", "　", "\t", "\n", "\r");
	$hou = array("", "", "", "", "");
	$url = str_replace($qian, $hou, $urls[$i]);
	$html = file_get_contents($url);
	$stocks = explode(';', $html);

	foreach ($stocks as $stock) {
		$stock = str_replace("\"", "", $stock);
		$shItems = explode(',', $stock);

		$names = explode('=', $shItems[0]);
		$names[1] = iconv('GB2312', 'utf-8//IGNORE', $names[1]);
		$code = substr($names[0], -8);

		if (count($shItems) < 33 || $shItems[1] <= 0 || $shItems[2] <= 0 || $shItems[3] <= 0 || $shItems[4] <= 0 || $shItems[5] <= 0 || $shItems[30] != $ct) {
			continue;
		}

		$ascCount = $dscCount = 0;
		$sWave = new Wave();
		$sWave -> asc = 1;
		$sWave -> high = 0;
		$sWave -> low = 0;
		$sWave -> beginTime = $sWave -> endTime = strtotime('09:31:00');
		$sWave -> id = uniqid();
		$sWave -> level = 1;
		$gw = null;
		
		if($kv->get($code . "sWave")){
			$ascCount = $kv->get($code . "ascCount");
			$dscCount = $kv->get($code . "dscCount");
			$sWave = $kv->get($code . "sWave");
			$gw = $kv->get($code . "gw");
		}else{
			$sql = "select wv,gw from waverecord where code = '$code' order by dt desc limit 1";
			$result = $mysql -> query($sql);
			if (!empty($result) && $row = $result -> fetch()) {
				$strWave = $row[0];
				$strgw = $row[1];
			
				if (!empty($strWave)) {
					$sWave = json_decode($strWave);
					$kv->set($code . "sWave", $sWave);
				}
				if (!empty($strgw)) {
					$gw = json_decode($strgw);
					$kv->set($code . "gw", $gw);
				}
			}
		}
		
		if (empty ( $gw ) || empty ( $sWave )) {
		
			$mail = new SaeMail ();
			$ret = $mail->quickSend ( 'hb_java@sina.com', 'ichess系统出错', $code, 'hb_java@sina.com', 'Tangyc_123' );
		
			// 发送失败时输出错误码和错误信息
			if ($ret === false)
				var_dump ( $mail->errno (), $mail->errmsg () );
		}
		
		if (time() > strtotime($as) && time() <= strtotime($ae)) {
			array_push($sqlSaves, "('$code','" . $ct . "','" . json_encode($sWave) . "','" . json_encode($gw) . "')");
			$kv->delete($ct);
			continue;
		}
		$shCurrentPrice = $shItems[3];
		$shClosePrice = $shItems[2];
		$openPrice = $shItems[1];
		$time = strtotime(date('Y-m-d H:i'));
		//$time = strtotime(substr($shItems[31], 0, strlen($shItems[31]) - 2) . '00');
		// 初始化
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

		// 如果股价向上走
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
					dealWave($gw, $sWave, $code, $names[1], $shCurrentPrice);

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
		} else {// 股价正在向下走
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
					dealWave($gw, $sWave, $code, $names[1], $shCurrentPrice);
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
		
		$kv->set($code . "ascCount", $ascCount);
		$kv->set($code . "dscCount", $dscCount);
		$kv->set($code . "sWave", $sWave);
		$kv->set($code . "gw", $gw);
		array_push($sqlInserts, " ('" . $code . "' , '" . $shItems[30] . "' , '" . $time . "' , '" . $names[1] . "' , '" . $shItems[2] . "' , '" . $shItems[1] . "' , '" . $shItems[3] . "' , '" . $shItems[4] . "' , '" . $shItems[5] . "' , '" . $shItems[8] . "' , '" . $shItems[9] . "' , '" . $shItems[9] / $shItems[8] . "' ) ");

	}
}
if (count($sqlSaves) > 0) {
	$sql = "INSERT INTO waverecord (code,dt,wv,gw) VALUES " . join(",", $sqlSaves);
	$mysql -> query($sql);
} else if(count($sqlInserts) > 0) {
	$sql = "INSERT INTO stockrecord (code, date, time, name, close, open, current, high, low, clmn, money, avg) VALUES " . join(",", $sqlInserts);
	$mysql -> query($sql);
}
$mysql -> close();

function saveDirector($code, $name, $price, $type, $level, $total,$arrow) {
	global $kv, $mysql, $rt;
	$strQuery = "INSERT INTO director(code, name, time, price, type, level, total,arrow) VALUES ('$code','$name','$rt',$price,$type,$level,$total,'$arrow')";

	$mysql -> query($strQuery);
}

function dealWave(&$gw, $w, $code, $name, $price) {
	global $mysql;
	$ow = json_decode(json_encode($gw));
	saveWave($gw, $w);
	$nw = $gw;
	if ($nw -> level == $ow -> level && $nw -> asc == $ow -> asc) {
		while (count($nw -> childWave) == count($ow -> childWave) && count($nw -> childWave) > 0) {
			$nw = $nw -> childWave[count($nw -> childWave) - 1];
			$ow = $ow -> childWave[count($ow -> childWave) - 1];
		}
	}
	$n = count($nw -> childWave);
	$lchild = $nw -> childWave[$n - 1];
	$brother = $nw -> childWave[$n - 2];
	$total = countArrow($gw);
	$arrow = getArrow($gw);
	if ($nw -> level > 5 && $nw -> asc && $lchild -> asc) {// 可以发出买入操作指令
		$strQuery = "UPDATE director SET flag = 1 where code = '$code' and flag = 0";
		$mysql -> query($strQuery);
		saveDirector($code, $name, $price, 1, $nw -> level, $total,$arrow);
	}
	if ($nw -> level > 5 && !$lchild -> asc) {// 可以发出卖出操作指令
		$strQuery = "UPDATE director SET flag = 1 where code = '$code' and flag = 0";
		$mysql -> query($strQuery);
		saveDirector($code, $name, $price, 0, $nw -> level, $total,$arrow);
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