<?php
header("Content-Type", "application/x-www-form-urlencoded; charset=utf8");
require '../header.php';

//overSell();
youkong();
short();
overSellAnd();
shortAnd();
junxian();
rate();
trans();
qiangchou();
//overBuy();
//attend();
//holder();
//ascend();
//descend();

function youkong() {
	global $mysql;
	$sql = "SELECT s.code, s.sell, s.buy, s.prefBuy, s.prefSell, s.current, s.high, s.low, s.concept FROM sign s INNER join (SELECT code FROM stockrecord where time = (SELECT max(time) from stockrecord) order by current/low DESC LIMIT 50) a on s.code=a.code where s.prefBuy > 0 order by s.current/s.prefBuy limit 30";

	$result = $mysql -> query($sql);
	$codes = array();
	
	while ($mr = $result -> fetch_array(MYSQLI_ASSOC)) {
		$code = strtolower($mr['code']);
		if (substr($code, 0, 2) == 'sh') {
			$code = '17:' . substr($code, 2, 6);
		} else if (substr($code, 0, 2) == 'sz') {
			$code = '33:' . substr($code, 2, 6);
		}
		array_push($codes, $code);
	}
	echo "23=" . join(",", $codes) . "</br>";
	mysqli_free_result($result);
}

function overSell() {
	global $mysql;
	$sql = "SELECT s.code,a.name, s.sell, s.buy, s.prefBuy, s.prefSell, s.current, s.high, s.low, s.concept FROM sign s
INNER join allstock a
on s.code=a.code
where s.prefBuy > 0 order by s.current/s.prefBuy limit 30";

	$result = $mysql -> query($sql);
	$codes = array();
	
	while ($mr = $result -> fetch_array(MYSQLI_ASSOC)) {
		$code = strtolower($mr['code']);
		if (substr($code, 0, 2) == 'sh') {
			$code = '17:' . substr($code, 2, 6);
		} else if (substr($code, 0, 2) == 'sz') {
			$code = '33:' . substr($code, 2, 6);
		}
		array_push($codes, $code);
	}
	echo "23=" . join(",", $codes) . "</br>";
	mysqli_free_result($result);
}

function short() {
	global $mysql;
	$sql = "SELECT s.code,a.name,s.buy as `signal`,'' as arrow,a.current,a.rate,a.current/a.avg as gate from ((SELECT code,buy from sign ) s INNER JOIN (SELECT code,name, current,avg,100*(current-close)/close as rate FROM stockrecord WHERE time = (SELECT max(time) from stockrecord) and current >= avg) a on s.code = a.code) order by gate desc limit 50";

	$result = $mysql -> query($sql);
	$codes = array();
	while ($mr = $result -> fetch_array(MYSQLI_ASSOC)) {
		$code = strtolower($mr['code']);
		if (substr($code, 0, 2) == 'sh') {
			$code = '17:' . substr($code, 2, 6);
		} else if (substr($code, 0, 2) == 'sz') {
			$code = '33:' . substr($code, 2, 6);
		}
		array_push($codes, $code);
	}
	echo "24=" . join(",", $codes) . "</br>";
	mysqli_free_result($result);
}

function overSellAnd() {
	global $mysql;
	$sql = "SELECT s.code,a.name, s.sell, s.buy, s.prefBuy, s.prefSell, s.current, s.high, s.low, s.concept FROM sign s
INNER join allstock a
on s.code=a.code
where s.prefBuy > 0 and s.current > s.avg and s.avg > 0 order by s.current/s.prefBuy limit 30";

	$result = $mysql -> query($sql);
	$codes = array();
	
	while ($mr = $result -> fetch_array(MYSQLI_ASSOC)) {
		$code = strtolower($mr['code']);
		if (substr($code, 0, 2) == 'sh') {
			$code = '17:' . substr($code, 2, 6);
		} else if (substr($code, 0, 2) == 'sz') {
			$code = '33:' . substr($code, 2, 6);
		}
		array_push($codes, $code);
	}
	echo "25=" . join(",", $codes) . "</br>";
	mysqli_free_result($result);
}

function shortAnd() {
	global $mysql;
	$sql = "SELECT s.code,a.name from (SELECT code,buy,current/avg as fiverate from sign where current >= avg) s INNER JOIN (SELECT code,name, current,avg, 100*(current-close)/close as rate FROM stockrecord WHERE time = (SELECT max(time) from stockrecord) order by current/avg desc limit 50) a on s.code = a.code order by s.fiverate";

	$result = $mysql -> query($sql);
	$codes = array();
	while ($mr = $result -> fetch_array(MYSQLI_ASSOC)) {
		$code = strtolower($mr['code']);
		if (substr($code, 0, 2) == 'sh') {
			$code = '17:' . substr($code, 2, 6);
		} else if (substr($code, 0, 2) == 'sz') {
			$code = '33:' . substr($code, 2, 6);
		}
		array_push($codes, $code);
	}
	echo "26=" . join(",", $codes) . "</br>";
	mysqli_free_result($result);
}

function junxian() {
	global $mysql;
	$sql = "SELECT code FROM sign WHERE avg > 0 and current > avg order by current/avg";

	$result = $mysql -> query($sql);
	$codes = array();
	
	while ($mr = $result -> fetch_array(MYSQLI_ASSOC)) {
		$code = strtolower($mr['code']);
		if (substr($code, 0, 2) == 'sh') {
			$code = '17:' . substr($code, 2, 6);
		} else if (substr($code, 0, 2) == 'sz') {
			$code = '33:' . substr($code, 2, 6);
		}
		array_push($codes, $code);
	}
	echo "27=" . join(",", $codes) . "</br>";
	mysqli_free_result($result);
}

function rate() {
	global $mysql, $icode;
	$t = $_REQUEST['t'];
	$urlIndex = "http://hq.sinajs.cn/list=$icode";

	$baseUrl = "http://hq.sinajs.cn/list=";
	$htmlIndex = file_get_contents($urlIndex);
	$htmlIndex = str_replace("\"", "", $htmlIndex);

	$items = explode(',', $htmlIndex);

	$indexRate = ($items[3] - $items[2]) / $items[2] * 100 + 4;

	$sqlIndex = "select close, current, date, time from indexrecord where code = '$icode' order by id desc limit 1";

	$tResult = $mysql -> query($sqlIndex);

	$row = mysqli_fetch_assoc($tResult);
	$ct = date("Y-m-d", strtotime("-1 day"));
	if (isset($row)) {
		if ($_GET['time']) {
			$time = $_GET['time'];
		} else {
			$time = $row["time"];
		}

		$sql = "SELECT distinct d.code,d.name FROM (SELECT distinct code,name,time,price,level FROM director WHERE (LEFT(code,2) = 'sh' or LEFT(code,2) = 'sz') and flag = 0) d inner JOIN (SELECT code, name,current, convert(increase,decimal(10,4)) as increase, convert(a,decimal(10,5)) as a, time from cand_rate where time = (SELECT MAX(time) from cand_rate)) s on d.code = s.code where s.current is not null ORDER by s.a desc limit 50 ";
		
		$result = $mysql -> query($sql);
		$codes = array();
		while ($mr = $result -> fetch_array(MYSQLI_ASSOC)) {
			$code = $mr['code'];
			if (substr($code, 0, 2) == 'sh') {
				$code = '17:' . substr($code, 2, 6);
			} else if (substr($code, 0, 2) == 'sz') {
				$code = '33:' . substr($code, 2, 6);
			}
			array_push($codes, $code);
		}
		echo "28=" . join(",", $codes) . "</br>";
		mysqli_free_result($result);
	}
}

function trans() {
	global $mysql, $icode;
	$t = $_REQUEST['t'];
	$urlIndex = "http://hq.sinajs.cn/list=$icode";
	$baseUrl = "http://hq.sinajs.cn/list=";
	$htmlIndex = file_get_contents($urlIndex);
	$htmlIndex = str_replace("\"", "", $htmlIndex);
	$items = explode(',', $htmlIndex);
	$indexRate = ($items[3] - $items[2]) / $items[2] * 100 + 4;

	$sqlIndex = "select distinct close, current, date, time from indexrecord where code = '$icode' order by id desc limit 1";

	$tResult = $mysql -> query($sqlIndex);

	$row = mysqli_fetch_assoc($tResult);
	if (isset($row)) {
		if ($_GET['time']) {
			$time = $_GET['time'];
		} else {
			$time = $row["time"];
		}

		$date = $row["date"];

		if (empty($t))
			$sql = "SELECT distinct d.code,d.name,s.trans,s.increase,s.current,d.time,d.level FROM (SELECT distinct code,name,time,price,level FROM director WHERE (LEFT(code,2) = 'sh' or LEFT(code,2) = 'sz') and flag = 0) d inner JOIN (SELECT code,trans,increase,current from cand_trans where time=(SELECT MAX(time) from cand_trans)) s on d.code = s.code where s.increase is not null ORDER by s.trans desc limit 50";
		else
			$sql = "SELECT distinct d.code,d.name,s.trans,s.increase,s.current,d.time,d.level FROM (SELECT distinct code,name,time,price,level FROM director WHERE (LEFT(code,2) = 'sh' or LEFT(code,2) = 'sz') and arrow REGEXP  '.1.1.1.1....' and flag = 0 and time <= '$t') d inner JOIN (SELECT code,trans,increase,current from cand_trans where time = '$t') s on d.code = s.code where s.increase is not null ORDER by s.trans desc ";

		$result = $mysql -> query($sql);
		$codes = array();
		while ($mr = $result -> fetch_array(MYSQLI_ASSOC)) {
			$code = $mr['code'];
			if (substr($code, 0, 2) == 'sh') {
				$code = '17:' . substr($code, 2, 6);
			} else if (substr($code, 0, 2) == 'sz') {
				$code = '33:' . substr($code, 2, 6);
			}
			array_push($codes, $code);
		}
		echo "29=" . join(",", $codes) . "</br>";
		mysqli_free_result($result);
	}
}

function qiangchou() {
	global $mysql;
	$sql = "SELECT r.code FROM stockrecord r inner join sign s on r.code = s.code WHERE r.low = r.open and r.time = (select max(time) from stockrecord) and s.avg > 0 and s.current > s.avg ";

	$result = $mysql -> query($sql);
	$codes = array();
	while ($mr = $result -> fetch_array(MYSQLI_ASSOC)) {
		$code = strtolower($mr['code']);
		if (substr($code, 0, 2) == 'sh') {
			$code = '17:' . substr($code, 2, 6);
		} else if (substr($code, 0, 2) == 'sz') {
			$code = '33:' . substr($code, 2, 6);
		}
		array_push($codes, $code);
	}
	echo "2A=" . join(",", $codes) . "</br>";
	mysqli_free_result($result);
}

function overBuy() {
	global $mysql;
	$sql = "SELECT s.code,a.name, s.sell, s.buy, s.prefBuy, s.prefSell, s.current, s.high, s.low, s.concept FROM sign s
INNER join allstock a
on s.code=a.code
where s.prefBuy > 0 order by s.current/s.prefSell desc limit 30";

	$result = $mysql -> query($sql);
	$codes = array();
	
	while ($mr = $result -> fetch_array(MYSQLI_ASSOC)) {
		$code = strtolower($mr['code']);
		if (substr($code, 0, 2) == 'sh') {
			$code = '17:' . substr($code, 2, 6);
		} else if (substr($code, 0, 2) == 'sz') {
			$code = '33:' . substr($code, 2, 6);
		}
		array_push($codes, $code);
	}
	echo "2A=" . join(",", $codes) . "</br>";
	mysqli_free_result($result);
}

function attend() {
	global $mysql;
	$sql = "SELECT a.code,a.name,s.buy as `signal`, '' as arrow,r.current,r.rate,r.current/r.avg as gate from attend a INNER JOIN sign s on a.code = s.code inner join (SELECT code,name, current,avg,100*(current-close)/close as rate FROM stockrecord WHERE time = (SELECT max(time) from stockrecord)) r on a.code = r.code order by s.buy desc";

	$result = $mysql -> query($sql);
	$codes = array();
	while ($mr = $result -> fetch_array(MYSQLI_ASSOC)) {
		$code = strtolower($mr['code']);
		if (substr($code, 0, 2) == 'sh') {
			$code = '17:' . substr($code, 2, 6);
		} else if (substr($code, 0, 2) == 'sz') {
			$code = '33:' . substr($code, 2, 6);
		}
		array_push($codes, $code);
	}
	echo "2C=" . join(",", $codes) . "</br>";
	mysqli_free_result($result);
}

function holder() {
	global $mysql;
	$sql = "SELECT a.code,a.name,s.sell as `signal`,'' as arrow,r.current,r.rate,r.current/r.avg as gate from holder a INNER JOIN sign s on a.code = s.code  inner join (SELECT code,name, current,avg,100*(current-close)/close as rate FROM stockrecord WHERE time = (SELECT max(time) from stockrecord)) r on a.code = r.code order by s.sell desc";

	$result = $mysql -> query($sql);
	$codes = array();
	while ($mr = $result -> fetch_array(MYSQLI_ASSOC)) {
		$code = strtolower($mr['code']);
		if (substr($code, 0, 2) == 'sh') {
			$code = '17:' . substr($code, 2, 6);
		} else if (substr($code, 0, 2) == 'sz') {
			$code = '33:' . substr($code, 2, 6);
		}
		array_push($codes, $code);
	}
	echo "2D=" . join(",", $codes) . "</br>";
	mysqli_free_result($result);
}

function ascend() {
	global $mysql;
	$sql = "SELECT distinct d.code FROM (SELECT distinct code,name,time,price,level FROM director WHERE (LEFT(code,2) = 'sh' or LEFT(code,2) = 'sz') and arrow REGEXP  '.1.1.1.1....' and flag = 0 ORDER by time desc ) d LEFT JOIN (SELECT code,current,100*(current-close)/close as rate FROM stockrecord WHERE time = (SELECT max(time) from stockrecord)) s on d.code = s.code where s.current is not null ORDER by d.time DESC, d.code ";

	$result = $mysql -> query($sql);
	$codes = array();
	while (!empty($result) && $mr = $result -> fetch_array(MYSQLI_ASSOC)) {
		$code = $mr['code'];
		if (substr($code, 0, 2) == 'sh') {
			$code = '17:' . substr($code, 2, 6);
		} else if (substr($code, 0, 2) == 'sz') {
			$code = '33:' . substr($code, 2, 6);
		}
		array_push($codes, $code);
	}
	echo "26=" . join(",", $codes) . "</br>";
	mysqli_free_result($result);
}

function descend() {
	global $mysql;
	$sql = "SELECT distinct d.code FROM (SELECT distinct code,name,time,price,level FROM director WHERE (LEFT(code,2) = 'sh' or LEFT(code,2) = 'sz') and arrow REGEXP  '.0.0.0.0....' and flag = 0 ORDER by time desc ) d LEFT JOIN (SELECT code,current,100*(current-close)/close as rate FROM stockrecord WHERE time = (SELECT max(time) from stockrecord)) s on d.code = s.code where s.current is not null ORDER by d.time DESC, d.code ";

	$result = $mysql -> query($sql);
	$codes = array();
	while (!empty($result) && $mr = $result -> fetch_array(MYSQLI_ASSOC)) {
		$code = $mr['code'];
		if (substr($code, 0, 2) == 'sh') {
			$code = '17:' . substr($code, 2, 6);
		} else if (substr($code, 0, 2) == 'sz') {
			$code = '33:' . substr($code, 2, 6);
		}
		array_push($codes, $code);
	}
	echo "27=" . join(",", $codes) . "</br>";
	mysqli_free_result($result);
}

$mysql -> close();
?>