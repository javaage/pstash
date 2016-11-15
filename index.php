<?php
require 'header.php';
require 'common.php';

//$c = $_REQUEST['c'];
//
//$rc = memcache_get($mmc, "rc");
//if (empty($rc)) {
//	$rc = 0;
//}
//
//if (!empty($c)) {
//	$rc = $c;
//}

// $code = $icode;
// recordWave($code);
// exit(0);

//$sql = 'DELETE FROM waverecord WHERE gw="null"';
//$mysql -> query($sql);

//recordWave('sz300288');
//exit(0);

$sql = "select preflist from candidate order by id desc limit 1";
echo $sql;
exit(0);
$result = $mysql -> query($sql);
if ($row = $result -> fetch()) {
	$candidates = preg_replace('/\s/', '', $row[0]);
	$candidates = strtolower($candidates);
	$listCode = explode(',', $candidates);
//	if ($rc >= count($listCode)) {
//		exit(0);
//	}
	
	for($k = 0; $k < count($listCode); $k++){
		$code = strtolower($listCode[$k]);
		recordWave($code);
	}
	exit(0);
	$code = strtolower($listCode[$rc]);
	
	while(!recordWave($code)){
		$rc++;
		if ($rc >= count($listCode)) {
			exit(0);
		}
		$code = strtolower($listCode[$rc]);
	}
	
	$rc++;
	//memcache_set($mmc, "rc", $rc, 0, 60 * 2);
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

function recordWave($code){
	global $mysql, $kv, $ycode;
	$gw = null;
	$ct = date('Y-m-d');
	$burl = "http://table.finance.yahoo.com/table.csv?s=$ycode";
	
	$baseUrl = "http://hq.sinajs.cn/list=";
	
	$sql = "SELECT code FROM waverecord WHERE code = '$code'";
	echo $sql;
	$result = $mysql -> query($sql);
	if($row = $result -> fetch()){
		echo "false";
		return false;
	}else{
		echo $code;
	}
	
	$csv = array();
	
	$url = $baseUrl . $code;
	$html = file_get_contents($url);

	$stock = str_replace("\"", "", $html);
	$items = explode(',', $stock);
	
	$ct = date('Y-m-d');
	$csv[] = array($ct, $items[3]);
	
	$burl = str_replace($ycode, substr($code, 2) . "." . substr($code, 0, 2), $burl);
	$burl = str_replace('sh', 'ss', $burl);

	$file = fopen($burl, 'r');
	while ($data = fgetcsv($file)) {
		if (is_numeric($data[6]))
			$csv[] = array($data[0], $data[6]);
	}
	
	for ($i = count($csv) - 1; $i > 0; $i--) {
		$f = $csv[$i];
		$l = $csv[$i - 1];
		$w = new Wave();
		$w -> id = uniqid();
		$w -> level = 5;
		$w -> asc = ($l[1] >= $f[1] ? 1 : 0);

		$n = $csv[$i - 2];
		while ((($n[1] >= $l[1]) == $w -> asc) && $i > 1) {
			$i--;
			$l = $csv[$i - 1];
			$n = $csv[$i - 2];
		}

		$w -> high = max($f[1], $l[1]);
		$w -> low = min($f[1], $l[1]);
		$w -> beginTime = strtotime($f[0] . "15:00:00");
		$w -> endTime = strtotime($l[0] . "15:00:00");

		saveWave($gw, $w);

	}
	
	$nw = $gw;
	while(count($nw->childWave)>0){
		$nw = $nw->childWave[count($nw->childWave)-1];
	}
	
	while($nw->level > 2){
		$child = json_decode(json_encode($nw));
		$child->id = uniqid();
		$child->pid = $nw->id;
		$child->level = $nw->level - 1;
		$nw->childWave[] = $child;
		$nw = $child;
	}
	
	if(empty($gw)){
		return false;
	}else{
		$strQuery = "INSERT INTO waverecord (code,dt,wv,gw) VALUES('$code','" . $ct . "','','" . json_encode($gw) . "')";
		$mysql -> query($strQuery);
		return true;
	}
}
?>