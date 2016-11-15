<?php
require 'header.php';
require 'common.php';
$ct = date('Y-m-d');

$mb = $ct . " 09:30:00";
$me = $ct . " 11:30:00";

$ab = $ct . " 13:00:00";
$as = $ct . " 14:57:00";
$ae = $ct . " 14:58:00";

if (!((time() >= strtotime($mb) && time() <= strtotime($me)) || (time() >= strtotime($ab) && time() <= strtotime($ae)))) {
	exit(0);
}

$ascCount = $dscCount = 0;
$dex = 0;
$sWave = new Wave();
$sWave -> asc = 1;
$sWave -> high = 0;
$sWave -> low = 0;
$sWave -> beginTime = $sWave -> endTime = strtotime('09:31:00');
$sWave -> id = uniqid();
$sWave -> level = 1;
$gw = null;

if ($kv -> get("sw")) {
	$ascCount = $kv -> get("asc");
	$dscCount = $kv -> get("dsc");
	$sWave = $kv -> get("sw");
	$gw = $kv -> get("g");
} else {
	$sql = "select wv,gw from waverecord where code='$icode' order by dt desc limit 1";
	$result = $mysql -> query($sql);
	if ($row = $result -> fetch()) {
		$strWave = $row[0];
		$strgw = $row[1];

		if (!empty($strWave)) {
			$sWave = json_decode($strWave);
			$kv -> set("sw", $sWave);
		}
		if (!empty($strgw)) {
			$gw = json_decode($strgw);
			$kv -> set("g", $gw);
		}
	}
}

if (empty($gw) || empty($sWave)) {

	$mail = new SaeMail();
	$ret = $mail -> quickSend('hb_java@sina.com', 'ichess系统出错', $icode, 'hb_java@sina.com', 'Tangyc_123');

	// 发送失败时输出错误码和错误信息
	if ($ret === false)
		var_dump($mail -> errno(), $mail -> errmsg());
}

if (time() > strtotime($as) && time() <= strtotime($ae)) {
	$strQuery = "INSERT INTO waverecord (code,dt,wv,gw) VALUES('$icode','" . $ct . "','" . json_encode($sWave) . "','" . json_encode($gw) . "')";
	$mysql -> query($strQuery);
	exit(0);
}

$timeFragment = 30;
$urlQueryIndex = "http://hq.sinajs.cn/list=sz399001,sh000001,sz399006,sz399005";
//echo "begin";
for ($cc = 0; $cc < 2; $cc++) {
//echo "cc=$cc";
	$time = strtotime(date('Y-m-d H:i'));
	$rt = date('Y-m-d H:i:s');
	$html = file_get_contents($urlQueryIndex);
	$html = str_replace("\"", "", $html);
	$stocks = explode(';', $html);
	$action = -1;
	if ($cc == 0) {
		//echo "1";
		$sqlInsert = [];
		for ($i = 0; $i < count($stocks) - 1; $i++) {
			$shItems = explode(',', $stocks[$i]);

			$shCurrentPrice = $shItems[3];
			$shClosePrice = $shItems[2];
			$openPrice = $shItems[1];
			
			//$time = strtotime(substr($shItems[31], 0, strlen($shItems[31]) - 2) . '00');
			$names = explode('=', $shItems[0]);
			$names[1] = iconv('GB2312', 'utf-8//IGNORE', $names[1]);
			$code = substr($names[0], -8);

			if ($shItems[30] == $ct) {
				$sqlInsert[] = "('" . $code . "' , '" . $shItems[30] . "' , '" . $time . "' , '" . $names[1] . "' , '" . $shItems[2] . "' , '" . $shItems[1] . "' , '" . $shItems[3] . "' , '" . $shItems[4] . "' , '" . $shItems[5] . "' , '" . $shItems[8] . "' , '" . $shItems[9] . "' , '" . $shItems[9] / $shItems[8] . "' )";
			}
		}
		$sql = "INSERT INTO indexrecord (code, date, time, name, close, open, current, high, low, clmn, money, avg) VALUES  " . join(",", $sqlInsert);
		$mysql -> query($sql);

		$sql = "select r.dex,convert(r.strong,decimal(6,2)) as strong,r.time from (select sh.current as dex,(sz.current -sh.current + (SELECT avg(current) from indexrecord where code='sh000001' order by id desc limit 2)) as strong,sz.time from (SELECT code,current * 1.3437 as current,time FROM indexrecord WHERE code='sz399006') sz inner join (SELECT code,current,time FROM indexrecord WHERE code='sh000001') sh on sz.time = sh.time ORDER by time desc LIMIT 2) r order by r.time ";

		$result = $mysql -> query($sql);
		$strongs = array();
		while ($mr = $result -> fetch_array(MYSQLI_ASSOC)) {
			$strongs[] = $mr;
		}
		
		if($strongs[1]['dex'] < $strongs[0]['dex']){
			if($strongs[1]['strong'] > $strongs[0]['strong']+3){
				$action = 3;
			}else if($strongs[1]['strong'] > $strongs[0]['strong']+2){
				$action = 2;
			}
		}
		if($strongs[1]['dex'] > $strongs[0]['dex']){
			if($strongs[1]['strong'] < $strongs[0]['strong']-3){
				$action = 1;
			}else if($strongs[1]['strong'] < $strongs[0]['strong']-2){
				$action = 0;
			}
		}
		mysqli_free_result($result);
		
		if($action > 0){
			$sql = "UPDATE stockaction SET action = $action,type = 3 where id in (select a.id from (SELECT max(id) as id from stockaction) a)";
			$mysql -> query($sql);
		}
	}
	//echo "2";
	$shItems = explode(',', $stocks[0]);

	$shCurrentPrice = $shItems[3];
	$shClosePrice = $shItems[2];
	$dex = 100 * ($shCurrentPrice - $shClosePrice) / $shClosePrice;
	$openPrice = $shItems[1];
	//$time = strtotime(substr($shItems[31], 0, strlen($shItems[31]) - 2) . '00');
	$fTime = strtotime('09:31:00');
	$lTime = $time;

	$names = explode('=', $shItems[0]);
	$names[1] = iconv('GB2312', 'utf-8//IGNORE', $names[1]);
	$code = substr($names[0], -8);
	
	if($shItems[30] != $ct){
		continue;
	}
	
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
				dealWave($gw, $sWave);

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
				dealWave($gw, $sWave);
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
	if ($cc == 0)
		sleep($timeFragment);
}
//echo "end";
$kv -> set("asc", $ascCount);
$kv -> set("dsc", $dscCount);
$kv -> set("sw", $sWave);
$kv -> set("g", $gw);

$mysql -> close();
function calrate($fTime, $lTime) {
	global $ct, $kv, $mysql, $rt;
	$sql = "select preflist from candidate order by id desc limit 1";
	$result = $mysql -> query($sql);

	if ($row = $result -> fetch()) {

		$candidates = preg_replace('/\s/', '', $row[0]);
		$codes = explode(',', $candidates);

		$ivalue = array();

		for ($i = 0; $i < count($codes) - 1; $i++) {
			$code = $codes[$i];

			$sqlmetric = "select time, current, (current-close)/close, name from stockrecord where code = '$code' and date = '$ct' ";

			$rmetric = $mysql -> query($sqlmetric);
			$metric = array();
			while ($mr = $rmetric -> fetch()) {
				array_push($metric, array($mr[0], $mr[1], $mr[2], $mr[3]));
			}
			mysqli_free_result($rmetric);

			if (count($metric) > 1) {
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
				for ($j = 0; $j < count($metric); $j++) {
					$sx += $metric[$j][0];
					$sy += $metric[$j][1];
					$sxy += $metric[$j][0] * $metric[$j][1];
					$sx2 += $metric[$j][0] * $metric[$j][0];
					$sy2 += $metric[$j][1] * $metric[$j][1];
				}
				$xavg = $sx / count($metric);
				$yavg = $sy / count($metric);
				for ($j = 0; $j < count($metric); $j++) {
					$sxd += ($metric[$j][0] - $xavg);
					$syd += ($metric[$j][1] - $yavg);
					$sxd2 += pow(($metric[$j][0] - $xavg), 2);
					$syd2 += pow(($metric[$j][1] - $yavg), 2);
				}

				$a = 10000 * (count($metric) * $sxy - $sx * $sy) / (count($metric) * $sx2 - $sx * $sx);
				$b = ($sx2 * $sy - $sx * $sxy) / (count($metric) * $sx2 - $sx * $sx);
				$r = sqrt($sxd2) * sqrt($syd2) == 0 ? 1 : abs($sxd * $syd) / (sqrt($sxd2) * sqrt($syd2));
				array_push($ivalue, "('" . $code . "', '" . $metric[count($metric) - 1][3] . "', " . $metric[count($metric) - 1][1] . "," . $a . ", " . $b . ", " . $r . ", " . 100 * $metric[count($metric) - 1][2] . ", '" . $rt . "')");
			}
		}

		mysqli_free_result($result);
		$sqlsave = "insert into cand_rate (code, name,current,a, b, r, increase, time) values " . join(",", $ivalue);
		$kv -> set("calrate", $sqlsave);
		$mysql -> query($sqlsave);
	}
}

function caltrans($fTime, $lTime) {
	global $ct, $kv, $mysql, $rt;
	if ($fTime < $lTime) {
		$sql = "INSERT cand_trans (code,name,current,increase,trans,time) SELECT r.code,r.name,r.current,r.increase,r.trans,'$rt' from (select f.code as code,f.name as name,l.current as current, 100 * (l.current - l.close)/l.close as increase,100 * (l.current - f.current)/f.close as trans from (SELECT code,name,close,current FROM `stockrecord` WHERE time >= " . $fTime . " and time < " . ($fTime + 60) . ") f LEFT JOIN (SELECT code,name,close,current FROM stockrecord WHERE time >= " . ($lTime - 60) . " AND time < " . $lTime . ") l ON f.code = l.code) r ";
		$mysql -> query($sql);
	}
}

function calbk($fTime, $lTime) {
	global $ct, $kv, $mysql, $rt;
	if ($fTime < $lTime) {
		$sql = "INSERT bk_trans (code, name,increase,trans,time) SELECT r.code,r.name,r.increase,r.trans,'$rt' from (select f.code as code,f.name as name, l.increase as increase, (l.increase - f.increase) as trans from (SELECT code,name,increase FROM bkrecord WHERE time >= " . $fTime . " and time < " . ($fTime + 60) . ") f LEFT JOIN (SELECT code,name,increase FROM bkrecord WHERE time >= " . ($lTime - 60) . " AND time < " . $lTime . ") l ON f.code = l.code) r ";

		$mysql -> query($sql);
	}
}

function calc($fTime, $lTime) {
	global $ct, $kv, $mysql, $rt;
	if ($fTime < $lTime) {
		$sql = "INSERT c_trans (code, name,increase,trans,time) SELECT r.code,r.name,r.increase,r.trans,'$rt' from (select f.code as code,f.name as name, l.increase as increase, (l.increase - f.increase) as trans from (SELECT code,name,increase FROM crecord WHERE time >= " . $fTime . " and time < " . ($fTime + 60) . ") f LEFT JOIN (SELECT code,name,increase FROM crecord WHERE time >= " . ($lTime - 60) . " AND time < " . $lTime . ") l ON f.code = l.code) r ";

		$mysql -> query($sql);
	}
}

function signal($fTime, $lTime, $asc) {
	global $mysql, $kv;
	if ($asc) {
		$strUpdate = "update sign set buy = (case when buy > 5 then buy - 5 else 0 end),sell = sell  + 1 where code in (select f.code  from (SELECT code,name,close,current FROM `stockrecord` WHERE time >= " . $fTime . " and time < " . ($fTime + 60) . ") f LEFT JOIN (SELECT code,name,close,current FROM stockrecord WHERE time >= " . ($lTime - 60) . " AND time < " . $lTime . ") l ON f.code = l.code where f.current > l.current)";

		$mysql -> query($strUpdate);
	} else {
		$strUpdate = "update sign set buy = buy  + 1, sell = (case when sell > 5 then sell - 5 else 0 end) where code in (select f.code  from (SELECT code,name,close,current FROM `stockrecord` WHERE time >= " . $fTime . " and time < " . ($fTime + 60) . ") f LEFT JOIN (SELECT code,name,close,current FROM stockrecord WHERE time >= " . ($lTime - 60) . " AND time < " . $lTime . ") l ON f.code = l.code where f.current < l.current)";

		$mysql -> query($strUpdate);
	}
}

function saveAction($action, $strWave, $strGw, $fTime, $lTime, $type, $content, $detail, $arrow, $pref, $strong, $dex) {
	global $kv, $mysql, $rt;

	$strQuery = "INSERT INTO stockaction (action,time,ftime,ltime,queue,gw,type,content,detail,arrow,pref,strong,dex) VALUES(" . $action . ",'" . $rt . "'," . $fTime . "," . $lTime . ",'" . $strWave . "','$strGw'," . $type . ",'$content','$detail','$arrow','$pref',$strong,$dex)";
	$kv -> set("saveAction", $strQuery);
	$mysql -> query($strQuery);
}

function saveHistory(&$node) {
	global $mysql;
	foreach ($node->childWave as $cw) {
		if (!empty($cw -> childWave)) {
			$sql = "insert into history(id,ftime,ltime,record,type) values('" . $cw -> id . "','" . $cw -> beginTime . "','" . $cw -> endTime . "','" . json_encode($cw -> childWave) . "','c')";
			$mysql -> query($sql);
			$cw -> count = count($cw -> childWave);
			$cw -> childWave = array();
		}
	}
}

function saveGlobal($g) {
	global $mysql;
	$sql = "insert into history(id,ftime,ltime,record,type) values('" . $g -> id . "','$g->beginTime','$g->endTime','" . json_encode($g) . "','g')";
	$mysql -> query($sql);
}

function dealWave(&$gw, $w) {
	//echo "3";
	global $kv, $sWave, $mysql, $dex, $action;

	$tls = [1 => "分时", 2 => "5分钟", 3 => "15分钟", 4 => "60分钟", 5 => "日线", 6 => "2日线", 7 => "周线", 8 => "月线", 9 => "半年线", 10 => "年线", 11 => "中长期", 12 => "中长期", 13 => "中长期", 14 => "中长期", 15 => "中长期", 16 => "中长期", 17 => "中长期"];
	$dir = [0 => "向下", 1 => "向上"];
	$indexDir = [0 => "观察深成指支撑", 1 => "观察深成指压力"];
	$todoList = ["，谨慎投资","，果断减仓", "，积极投资", "，果断加仓"];

	$ow = json_decode(json_encode($gw));
	saveWave($gw, $w);

	$action = -1;
	$type = -1;
	$l2 = $gw;
	while ($l2 -> level > 2) {
		$l2 = $l2 -> childWave[count($l2 -> childWave) - 1];
	}
	$cl2 = count($l2 -> childWave);
	if ($cl2 > 1) {
		$b1 = $l2 -> childWave[$cl2 - 2];
		$l1 = $l2 -> childWave[$cl2 - 1];
		$lTime = $l1 -> endTime;
		if ($l2 -> asc) {
			if ($l1 -> asc) {
				$fTime = $b1 -> beginTime;
			} else {
				$fTime = $l2 -> beginTime;
			}
			$type = 1;
			signal($fTime, $lTime, 1);
		} else {
			if (!$l1 -> asc) {
				$fTime = $b1 -> beginTime;
			} else {
				$fTime = $l2 -> beginTime;
			}
			$type = 0;
			signal($fTime, $lTime, 0);
			cal($fTime, $lTime);
		}
	}

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
	$span = number_format(($brother -> endTime - $brother -> beginTime) / 3600.0, 0);

	$lTime = $lchild -> endTime;
	if ($nw -> asc) {
		if ($lchild -> asc) {
			$p = $brother -> high;
			$fTime = $brother -> beginTime;
		} else {
			$p = $nw -> low;
			$fTime = $nw -> beginTime;
		}
	} else {
		if (!$lchild -> asc) {
			$p = $brother -> low;
			$fTime = $brother -> beginTime;
		} else {
			$p = $nw -> high;
			$fTime = $nw -> beginTime;
		}
	}

	$sql = "SELECT s.code,a.name from (SELECT code,buy,current/avg as fiverate from sign where buy > 0 and current >= avg) s INNER JOIN (SELECT code,name, current,avg, 100*(current-close)/close as rate FROM stockrecord WHERE time = (SELECT max(time) from stockrecord) order by current/avg desc limit 50) a on s.code = a.code order by s.fiverate limit 30";

	$result = $mysql -> query($sql);
	$names = array();
	while ($mr = $result -> fetch_array(MYSQLI_ASSOC)) {
		$name = strtolower($mr['name']);
		array_push($names, $name);
	}
	$pref = join(",", $names);
	mysqli_free_result($result);
	$strong = popular($w -> beginTime, $w -> endTime, $w -> asc);

	$content = $n . "、" . $tls[min($lchild -> level, 17)] . $dir[$lchild -> asc] . $todoList[$action];
	$detail = $tls[min($nw -> level, 17)] . $dir[$nw -> asc] . "，关键点" . number_format($p, 0);
	
	saveAction($action, json_encode($sWave), json_encode($gw), $fTime, $lTime, $type, $content, $detail, $arrow, $pref, $strong, $dex);
}

function cal($fTime, $lTime) {
	//echo "4";
	calrate($fTime, $lTime);
	caltrans($fTime, $lTime);
	calbk($fTime, $lTime);
	calc($fTime, $lTime);
}
?>