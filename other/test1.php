<?php
require '../header.php';
require '../common.php';

$ct = '2016-02-25';
$urls = $kv -> get($ct);
echo json_encode($urls);
exit(0);
	$gw = $kv -> get("g");
	echo json_encode($gw) . "</br>";
	$l2 = $gw;
	while($l2->level > 2){
		$l2 = $l2 -> childWave[count($l2 -> childWave) - 1];
	}
	$cl2 = count($l2 -> childWave);
	if($cl2 > 1){
		$b1 = $l2 -> childWave[$cl2 - 3];
		$l1 = $l2 -> childWave[$cl2 - 2];
		$lTime = $l1 -> endTime;
		if ($l2 -> asc) {
			if ($l1 -> asc) {
				$fTime = $b1 -> beginTime;
			} else {
				$fTime = $l2 -> beginTime;
			}
			signal($fTime, $lTime, 1);
		} else {
			if (!$l1 -> asc) {
				$fTime = $b1 -> beginTime;
			} else {
				$fTime = $l2 -> beginTime;
			}
			signal($fTime, $lTime, 0);
		}
	}
function signal($fTime, $lTime, $asc) {
	global $mysql, $kv;
	if ($asc) {
		$strUpdate = "update sign set buy = (case when buy > 5 then buy - 5 else 0 end),sell = sell  + 1 where code in (select f.code  from (SELECT code,name,close,current FROM `stockrecord` WHERE time >= " . $fTime . " and time < " . ($fTime + 60) . ") f LEFT JOIN (SELECT code,name,close,current FROM stockrecord WHERE time >= " . ($lTime - 60) . " AND time < " . $lTime . ") l ON f.code = l.code where f.current > l.current)";
		echo $strUpdate;
		$mysql -> query($strUpdate);
	} else {
		$strUpdate = "update sign set buy = buy  + 1, sell = (case when sell > 5 then sell - 5 else 0 end) where code in (select f.code  from (SELECT code,name,close,current FROM `stockrecord` WHERE time >= " . $fTime . " and time < " . ($fTime + 60) . ") f LEFT JOIN (SELECT code,name,close,current FROM stockrecord WHERE time >= " . ($lTime - 60) . " AND time < " . $lTime . ") l ON f.code = l.code where f.current < l.current)";
		echo $strUpdate;
		$mysql -> query($strUpdate);
	}
}
exit(0);

$urls = getUrl();
echo json_encode($urls);


$sql = "SELECT s.code,a.name from (SELECT code,buy from sign where buy > 0 order by buy desc limit 50) s INNER JOIN (SELECT code,name, current,100*(current-close)/close as rate FROM stockrecord WHERE current > avg and time = (SELECT max(time) from stockrecord)) a on s.code = a.code order by buy desc, a.rate limit 30";
echo $sql;
	$result = $mysql -> query($sql);
	$names = array();
	while ($mr = $result -> fetch_array(MYSQLI_ASSOC)) {
		$name = strtolower($mr['name']);
		array_push($names, $name);
	}
	$pref = join(",", $names);
	echo $pref;
	mysqli_free_result($result);
exit(0);
$sql = "SELECT DISTINCT code from waverecord WHERE (LEFT(code,2)='sh' or LEFT(code,2)='sz') and code <> '$icode'";
$result = $mysql -> query($sql);

$arr = array();

while ($row = $result -> fetch()) {
	$code = $row[0];
	$arr[] = "('$code')";
}

$sqlInserts = "insert into signal (code) values " . join(",", $arr);
echo $sqlInserts;
$mysql -> query($sqlInserts);

exit(0);
$c = $_REQUEST['c'];

if (empty($c)) {
	$c = 100000;
}
/*
 $strgw = memcache_get ( $mmc, "gw" );
 echo $strgw;
 exit(0);

 $gw = json_decode ( $strgw );
 $s = '{"id":"564ebcaa2eeb9","pid":"564ebecf17603","level":2,"asc":0,"high":"12743.716","low":"12618.837","beginTime":1448000160,"endTime":1448001600,"childWave":
 [{"id":"564ebae88ccb1","pid":"564ebcaa2eeb9","level":1,"asc":0,"high":"12743.716","low":"12730.690","beginTime":1448000160,"endTime":1448000400,"childWave":[]},
 {"id":"564ebbf52b387","pid":"564ebcaa2eeb9","level":1,"asc":1,"high":"12734.712","low":"12730.690","beginTime":1448000400,"endTime":1448000580,"childWave":[]},
 {"id":"564ebcaa2ef09","pid":"564ebcaa2eeb9","level":1,"asc":0,"high":"12734.712","low":"12678.903","beginTime":1448000580,"endTime":1448000940,"childWave":[]},
 {"id":"564ebe13557d4","pid":"564ebcaa2eeb9","level":1,"asc":1,"high":"12699.280","low":"12678.903","beginTime":1448000940,"endTime":1448001180,"childWave":[]},
 {"id":"564ebf019f217","pid":"564ebcaa2eeb9","level":1,"asc":0,"high":"12699.280","low":"12618.837","beginTime":1448001180,"endTime":1448001600,"childWave":[]},
 {"id":"564ec089b3b43","pid":"564ebcaa2eeb9","level":1,"asc":1,"high":"12665.124","low":"12618.837","beginTime":1448001600,"endTime":1448001840,"childWave":[]},
 {"id":"564ec1776201a","pid":"564ebcaa2eeb9","level":1,"asc":0,"high":"12665.124","low":"12639.228","beginTime":1448001840,"endTime":1448002080,"childWave":[]}]}';

 echo saveWave ( $gw, json_decode ( $s ) );
 echo json_encode ( $gw );
 exit(0);
 */
$csv = array();
$file = fopen('http://table.finance.yahoo.com/table.csv?s=$ycode','r'); 
while ($data = fgetcsv($file)) {
	if(is_numeric($data[1]))
		$csv[] = $data;
} 

for ($i = count($csv) - 1; $i > 0; $i--) {
	$f = $csv[$i];
	$l = $csv[$i - 1];
	$w = new Wave();
	$w -> id = uniqid();
	$w -> level = 5;
	$w -> asc = ($l[1] > $f[1] ? 1 : 0);
	
	$n = $csv[$i - 2];
	while((($n[1] > $l[1])==$w -> asc) && $i >1){
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

$s[] = '{"id":"564d263a886e8","pid":null,"level":2,"asc":1,"high":"12387.503","low":"12257.04","beginTime":1447896600,"endTime":1447897020,"childWave":
[{"id":"564d263a886a9","pid":"564d263a886e8","level":1,"asc":1,"high":"12373.620","low":"12283.758","beginTime":1447896600,"endTime":1447896600,"childWave":[]},
{"id":"564d269511a34","pid":"564d263a886e8","level":1,"asc":0,"high":"12373.620","low":"12331.483","beginTime":1447896600,"endTime":1447896780,"childWave":[]},
{"id":"564d272c3f041","pid":"564d263a886e8","level":1,"asc":1,"high":"12387.503","low":"12331.483","beginTime":1447896780,"endTime":1447897020,"childWave":[]}]}';

$s[] = '{"id":"564d294871869","pid":null,"level":2,"asc":0,"high":"12387.503","low":"12309.785","beginTime":1447897020,"endTime":1447897560,"childWave":
[{"id":"564d281bc9498","pid":"564d294871869","level":1,"asc":0,"high":"12387.503","low":"12358.169","beginTime":1447897020,"endTime":1447897200,"childWave":[]},
{"id":"564d28ce42764","pid":"564d294871869","level":1,"asc":1,"high":"12359.486","low":"12358.169","beginTime":1447897200,"endTime":1447897260,"childWave":[]},
{"id":"564d2948718bd","pid":"564d294871869","level":1,"asc":0,"high":"12359.486","low":"12309.785","beginTime":1447897260,"endTime":1447897560,"childWave":[]},
{"id":"564d2a5819d25","pid":"564d294871869","level":1,"asc":1,"high":"12351.711","low":"12309.785","beginTime":1447897560,"endTime":1447897800,"childWave":[]},
{"id":"564d2b2ec5ce1","pid":"564d294871869","level":1,"asc":0,"high":"12351.711","low":"12333.629","beginTime":1447897800,"endTime":1447897920,"childWave":[]}]}';

$s[] = '{"id":"564d2bbec2676","pid":null,"level":2,"asc":1,"high":"12426.436","low":"12309.785","beginTime":1447897560,"endTime":1447898700,"childWave":
[{"id":"564d2bbec2853","pid":"564d2bbec2676","level":1,"asc":1,"high":"12388.874","low":"12333.629","beginTime":1447897920,"endTime":1447898220,"childWave":[]},
{"id":"564d2ccac5787","pid":"564d2bbec2676","level":1,"asc":0,"high":"12388.874","low":"12374.418","beginTime":1447898220,"endTime":1447898400,"childWave":[]},
{"id":"564d2d7e724dc","pid":"564d2bbec2676","level":1,"asc":1,"high":"12426.436","low":"12374.418","beginTime":1447898400,"endTime":1447898700,"childWave":[]}]}';

$s[] = '{"id":"564d2ff75e719","pid":null,"level":2,"asc":0,"high":"12426.436","low":"12381.113","beginTime":1447898700,"endTime":1447899240,"childWave":
[{"id":"564d2eca163e8","pid":"564d2ff75e719","level":1,"asc":0,"high":"12426.436","low":"12400.805","beginTime":1447898700,"endTime":1447898940,"childWave":[]},
{"id":"564d2f9b11f01","pid":"564d2ff75e719","level":1,"asc":1,"high":"12401.194","low":"12400.805","beginTime":1447898940,"endTime":1447899060,"childWave":[]},
{"id":"564d2ff75e76d","pid":"564d2ff75e719","level":1,"asc":0,"high":"12401.194","low":"12381.113","beginTime":1447899060,"endTime":1447899240,"childWave":[]},
{"id":"564d30c8343d6","pid":"564d2ff75e719","level":1,"asc":1,"high":"12400.871","low":"12381.113","beginTime":1447899240,"endTime":1447899540,"childWave":[]},
{"id":"564d31f31c943","pid":"564d2ff75e719","level":1,"asc":0,"high":"12400.871","low":"12384.769","beginTime":1447899540,"endTime":1447899780,"childWave":[]}]}';

$s[] = '{"id":"564d32e40e13b","pid":null,"level":2,"asc":1,"high":"12438.097","low":"12381.113","beginTime":1447899240,"endTime":1447900740,"childWave":
[{"id":"564d32e40e2e4","pid":"564d32e40e13b","level":1,"asc":1,"high":"12436.874","low":"12384.769","beginTime":1447899780,"endTime":1447900200,"childWave":[]},
{"id":"564d3487333ea","pid":"564d32e40e13b","level":1,"asc":0,"high":"12436.874","low":"12423.226","beginTime":1447900200,"endTime":1447900500,"childWave":[]},
{"id":"564d35b304bd3","pid":"564d32e40e13b","level":1,"asc":1,"high":"12438.097","low":"12423.226","beginTime":1447900500,"endTime":1447900740,"childWave":[]}]}';

$s[] = '{"id":"564d38a1be111","pid":null,"level":2,"asc":0,"high":"12438.097","low":"12310.038","beginTime":1447900740,"endTime":1447902420,"childWave":
[{"id":"564d36a28d90d","pid":"564d38a1be111","level":1,"asc":0,"high":"12438.097","low":"12394.881","beginTime":1447900740,"endTime":1447901040,"childWave":[]},
{"id":"564d37cf3fc2d","pid":"564d38a1be111","level":1,"asc":1,"high":"12412.927","low":"12394.881","beginTime":1447901040,"endTime":1447901220,"childWave":[]},
{"id":"564d38a1be15b","pid":"564d38a1be111","level":1,"asc":0,"high":"12412.927","low":"12386.855","beginTime":1447901220,"endTime":1447901400,"childWave":[]},
{"id":"564d399236a17","pid":"564d38a1be111","level":1,"asc":1,"high":"12403.424","low":"12386.855","beginTime":1447901400,"endTime":1447901640,"childWave":[]},
{"id":"564d3a274cbc7","pid":"564d38a1be111","level":1,"asc":0,"high":"12403.424","low":"12349.267","beginTime":1447901640,"endTime":1447901940,"childWave":[]},
{"id":"564d3b7321292","pid":"564d38a1be111","level":1,"asc":1,"high":"12365.625","low":"12349.267","beginTime":1447901940,"endTime":1447902180,"childWave":[]},
{"id":"564d3c43378b3","pid":"564d38a1be111","level":1,"asc":0,"high":"12365.625","low":"12310.038","beginTime":1447902180,"endTime":1447902420,"childWave":[]},
{"id":"564d3d5464d2a","pid":"564d38a1be111","level":1,"asc":1,"high":"12345.205","low":"12310.038","beginTime":1447902420,"endTime":1447902660,"childWave":[]},
{"id":"564d3e226a9d0","pid":"564d38a1be111","level":1,"asc":0,"high":"12345.205","low":"12337.318","beginTime":1447902660,"endTime":1447902840,"childWave":[]}]}';

$s[] = '{"id":"564d3ed8e1c61","pid":null,"level":2,"asc":1,"high":"12370.685","low":"12310.038","beginTime":1447902420,"endTime":1447903080,"childWave":
[{"id":"564d3ed8e1d7c","pid":"564d3ed8e1c61","level":1,"asc":1,"high":"12370.685","low":"12337.318","beginTime":1447902840,"endTime":1447903080,"childWave":[]}]}';

$s[] = '{"id":"564d412ed7538","pid":null,"level":2,"asc":0,"high":"12370.685","low":"12333.390","beginTime":1447903080,"endTime":1447909800,"childWave":
[{"id":"564d3fe697499","pid":"564d412ed7538","level":1,"asc":0,"high":"12370.685","low":"12357.862","beginTime":1447903080,"endTime":1447903320,"childWave":[]},
{"id":"564d40b6a2d05","pid":"564d412ed7538","level":1,"asc":1,"high":"12357.927","low":"12357.862","beginTime":1447903320,"endTime":1447903380,"childWave":[]},
{"id":"564d412ed7589","pid":"564d412ed7538","level":1,"asc":0,"high":"12357.927","low":"12336.924","beginTime":1447903380,"endTime":1447903680,"childWave":[]},
{"id":"564d423e5ecde","pid":"564d412ed7538","level":1,"asc":1,"high":"12357.787","low":"12336.924","beginTime":1447903680,"endTime":1447909380,"childWave":[]},
{"id":"564d5881b913f","pid":"564d412ed7538","level":1,"asc":0,"high":"12357.787","low":"12333.390","beginTime":1447909380,"endTime":1447909800,"childWave":[]}]}';

$s[] = '{"id":"564d5d4f18f59","pid":null,"level":2,"asc":1,"high":"12456.279","low":"12333.390","beginTime":1447909800,"endTime":1447911240,"childWave":
[{"id":"564d59ea8195e","pid":"564d5d4f18f59","level":1,"asc":1,"high":"12429.640","low":"12333.390","beginTime":1447909800,"endTime":1447910640,"childWave":[]},
{"id":"564d5d4f18fab","pid":"564d5d4f18f59","level":1,"asc":0,"high":"12429.640","low":"12411.038","beginTime":1447910640,"endTime":1447910880,"childWave":[]},
{"id":"564d5e406df85","pid":"564d5d4f18f59","level":1,"asc":1,"high":"12456.279","low":"12411.038","beginTime":1447910880,"endTime":1447911240,"childWave":[]}]}';

$s[] = '{"id":"564d612c9395d","pid":null,"level":2,"asc":0,"high":"12456.279","low":"12420.100","beginTime":1447911240,"endTime":1447911780,"childWave":
[{"id":"564d5fa74d8ca","pid":"564d612c9395d","level":1,"asc":0,"high":"12456.279","low":"12430.384","beginTime":1447911240,"endTime":1447911480,"childWave":[]},
{"id":"564d60b6343a1","pid":"564d612c9395d","level":1,"asc":1,"high":"12431.859","low":"12430.384","beginTime":1447911480,"endTime":1447911540,"childWave":[]},
{"id":"564d612c939b0","pid":"564d612c9395d","level":1,"asc":0,"high":"12431.859","low":"12420.100","beginTime":1447911540,"endTime":1447911780,"childWave":[]}]}';

$s[] = '{"id":"564d632adc7f2","pid":null,"level":2,"asc":1,"high":"12526.431","low":"12420.100","beginTime":1447911780,"endTime":1447912740,"childWave":
[{"id":"564d61c622214","pid":"564d632adc7f2","level":1,"asc":1,"high":"12468.493","low":"12420.100","beginTime":1447911780,"endTime":1447912140,"childWave":[]},
{"id":"564d632adc842","pid":"564d632adc7f2","level":1,"asc":0,"high":"12468.493","low":"12461.072","beginTime":1447912140,"endTime":1447912380,"childWave":[]},
{"id":"564d64390e5cf","pid":"564d632adc7f2","level":1,"asc":1,"high":"12526.431","low":"12461.072","beginTime":1447912380,"endTime":1447912740,"childWave":[]}]}';

$s[] = '{"id":"564d6744d6652","pid":null,"level":2,"asc":0,"high":"12526.431","low":"12459.374","beginTime":1447912740,"endTime":1447913400,"childWave":
[{"id":"564d65a0a3a62","pid":"564d6744d6652","level":1,"asc":0,"high":"12526.431","low":"12490.719","beginTime":1447912740,"endTime":1447912980,"childWave":[]},
{"id":"564d669138b47","pid":"564d6744d6652","level":1,"asc":1,"high":"12500.104","low":"12490.719","beginTime":1447912980,"endTime":1447913160,"childWave":[]},
{"id":"564d6744d66a7","pid":"564d6744d6652","level":1,"asc":0,"high":"12500.104","low":"12459.374","beginTime":1447913160,"endTime":1447913400,"childWave":[]},
{"id":"564d6818c2861","pid":"564d6744d6652","level":1,"asc":1,"high":"12478.383","low":"12459.374","beginTime":1447913400,"endTime":1447913640,"childWave":[]},
{"id":"564d68e9a3aca","pid":"564d6744d6652","level":1,"asc":0,"high":"12478.383","low":"12470.758","beginTime":1447913640,"endTime":1447913820,"childWave":[]}]}';

$s[] = '{"id":"564d69bcd1e51","pid":null,"level":2,"asc":1,"high":"12715.992","low":"12459.374","beginTime":1447913400,"endTime":1447985280,"childWave":
[{"id":"564d69bcd1f6e","pid":"564d69bcd1e51","level":1,"asc":1,"high":"12511.555","low":"12470.758","beginTime":1447913820,"endTime":1447914180,"childWave":[]},
{"id":"564d6b247d1fc","pid":"564d69bcd1e51","level":1,"asc":0,"high":"12511.555","low":"12497.104","beginTime":1447914180,"endTime":1447914360,"childWave":[]},
{"id":"564d6bf55817e","pid":"564d69bcd1e51","level":1,"asc":1,"high":"12554.096","low":"12497.104","beginTime":1447914360,"endTime":1447914720,"childWave":[]},
{"id":"564d6d3e404e8","pid":"564d69bcd1e51","level":1,"asc":0,"high":"12554.096","low":"12548.882","beginTime":1447914720,"endTime":1447914960,"childWave":[]},
{"id":"564d6e2f5c8b8","pid":"564d69bcd1e51","level":1,"asc":1,"high":"12595.905","low":"12548.882","beginTime":1447914960,"endTime":1447915320,"childWave":[]},
{"id":"564d6f97b7c59","pid":"564d69bcd1e51","level":1,"asc":0,"high":"12595.905","low":"12564.313","beginTime":1447915320,"endTime":1447915560,"childWave":[]},
{"id":"564d70a4f3aac","pid":"564d69bcd1e51","level":1,"asc":1,"high":"12602.170","low":"12564.313","beginTime":1447915560,"endTime":1447916160,"childWave":[]},
{"id":"564d72fe8bea2","pid":"564d69bcd1e51","level":1,"asc":0,"high":"12602.170","low":"12601.293","beginTime":1447916160,"endTime":1447916220,"childWave":[]},
{"id":"564e7b0c41f29","pid":"564d69bcd1e51","level":1,"asc":1,"high":"12651.627","low":"12601.293","beginTime":1447916220,"endTime":1447983900,"childWave":[]},
{"id":"564e7b8157044","pid":"564d69bcd1e51","level":1,"asc":0,"high":"12651.627","low":"12619.378","beginTime":1447983900,"endTime":1447984260,"childWave":[]},
{"id":"564e7cca83acd","pid":"564d69bcd1e51","level":1,"asc":1,"high":"12680.857","low":"12619.378","beginTime":1447984260,"endTime":1447984740,"childWave":[]},
{"id":"564e7eab5ea28","pid":"564d69bcd1e51","level":1,"asc":0,"high":"12680.857","low":"12667.939","beginTime":1447984740,"endTime":1447984980,"childWave":[]},
{"id":"564e7fb884aca","pid":"564d69bcd1e51","level":1,"asc":1,"high":"12715.992","low":"12667.939","beginTime":1447984980,"endTime":1447985280,"childWave":[]}]}';

$s[] = '{"id":"564e8288ee3e1","pid":null,"level":2,"asc":0,"high":"12715.992","low":"12599.916","beginTime":1447985280,"endTime":1447986420,"childWave":
[{"id":"564e80c8a2f60","pid":"564e8288ee3e1","level":1,"asc":0,"high":"12715.992","low":"12685.603","beginTime":1447985280,"endTime":1447985580,"childWave":[]},
{"id":"564e82105648c","pid":"564e8288ee3e1","level":1,"asc":1,"high":"12691.064","low":"12685.603","beginTime":1447985580,"endTime":1447985700,"childWave":[]},
{"id":"564e8288ee434","pid":"564e8288ee3e1","level":1,"asc":0,"high":"12691.064","low":"12632.585","beginTime":1447985700,"endTime":1447986000,"childWave":[]},
{"id":"564e8398c2413","pid":"564e8288ee3e1","level":1,"asc":1,"high":"12647.470","low":"12632.585","beginTime":1447986000,"endTime":1447986120,"childWave":[]},
{"id":"564e842d04f7d","pid":"564e8288ee3e1","level":1,"asc":0,"high":"12647.470","low":"12599.916","beginTime":1447986120,"endTime":1447986420,"childWave":[]},
{"id":"564e853c53194","pid":"564e8288ee3e1","level":1,"asc":1,"high":"12638.070","low":"12599.916","beginTime":1447986420,"endTime":1447986660,"childWave":[]},
{"id":"564e862c14986","pid":"564e8288ee3e1","level":1,"asc":0,"high":"12638.070","low":"12629.969","beginTime":1447986660,"endTime":1447986780,"childWave":[]}]}';

$s[] = '{"id":"564e86c2514ba","pid":null,"level":2,"asc":1,"high":"12667.299","low":"12599.916","beginTime":1447986420,"endTime":1447987620,"childWave":
[{"id":"564e86c251687","pid":"564e86c2514ba","level":1,"asc":1,"high":"12659.907","low":"12629.969","beginTime":1447986780,"endTime":1447987020,"childWave":[]},
{"id":"564e87b0d21ba","pid":"564e86c2514ba","level":1,"asc":0,"high":"12659.907","low":"12646.561","beginTime":1447987020,"endTime":1447987260,"childWave":[]},
{"id":"564e88848377d","pid":"564e86c2514ba","level":1,"asc":1,"high":"12667.299","low":"12646.561","beginTime":1447987260,"endTime":1447987620,"childWave":[]}]}';

$s[] = '{"id":"564e8bacbec5c","pid":null,"level":2,"asc":0,"high":"12667.299","low":"12566.093","beginTime":1447987620,"endTime":1447989060,"childWave":
[{"id":"564e89eb681c8","pid":"564e8bacbec5c","level":1,"asc":0,"high":"12667.299","low":"12645.741","beginTime":1447987620,"endTime":1447987860,"childWave":[]},
{"id":"564e8adac11d2","pid":"564e8bacbec5c","level":1,"asc":1,"high":"12656.857","low":"12645.741","beginTime":1447987860,"endTime":1447988040,"childWave":[]},
{"id":"564e8bacbecb3","pid":"564e8bacbec5c","level":1,"asc":0,"high":"12656.857","low":"12627.763","beginTime":1447988040,"endTime":1447988400,"childWave":[]},
{"id":"564e8cf8c8cd5","pid":"564e8bacbec5c","level":1,"asc":1,"high":"12630.501","low":"12627.763","beginTime":1447988400,"endTime":1447988520,"childWave":[]},
{"id":"564e8d7086a85","pid":"564e8bacbec5c","level":1,"asc":0,"high":"12630.501","low":"12627.443","beginTime":1447988520,"endTime":1447988640,"childWave":[]},
{"id":"564e8e07dfc9d","pid":"564e8bacbec5c","level":1,"asc":1,"high":"12628.912","low":"12627.443","beginTime":1447988640,"endTime":1447988700,"childWave":[]},
{"id":"564e8e7e0c581","pid":"564e8bacbec5c","level":1,"asc":0,"high":"12628.912","low":"12566.093","beginTime":1447988700,"endTime":1447989060,"childWave":[]},
{"id":"564e8f8e96938","pid":"564e8bacbec5c","level":1,"asc":1,"high":"12612.423","low":"12566.093","beginTime":1447989060,"endTime":1447989300,"childWave":[]},
{"id":"564e907cb681d","pid":"564e8bacbec5c","level":1,"asc":0,"high":"12612.423","low":"12604.187","beginTime":1447989300,"endTime":1447989480,"childWave":[]}]}';

$s[] = '{"id":"564e91315f794","pid":null,"level":2,"asc":1,"high":"12738.618","low":"12566.093","beginTime":1447989060,"endTime":1447997760,"childWave":
[{"id":"564e91315f8ad","pid":"564e91315f794","level":1,"asc":1,"high":"12650.420","low":"12604.187","beginTime":1447989480,"endTime":1447989780,"childWave":[]},
{"id":"564e927968c04","pid":"564e91315f794","level":1,"asc":0,"high":"12650.420","low":"12633.975","beginTime":1447989780,"endTime":1447990080,"childWave":[]},
{"id":"564e9386e0773","pid":"564e91315f794","level":1,"asc":1,"high":"12663.654","low":"12633.975","beginTime":1447990080,"endTime":1447995720,"childWave":[]},
{"id":"564ea98e71a3a","pid":"564e91315f794","level":1,"asc":0,"high":"12663.654","low":"12651.571","beginTime":1447995720,"endTime":1447995900,"childWave":[]},
{"id":"564eaa61570c2","pid":"564e91315f794","level":1,"asc":1,"high":"12696.451","low":"12651.571","beginTime":1447995900,"endTime":1447996560,"childWave":[]},
{"id":"564eacf648b6c","pid":"564e91315f794","level":1,"asc":0,"high":"12696.451","low":"12691.111","beginTime":1447996560,"endTime":1447996680,"childWave":[]},
{"id":"564ead6e30608","pid":"564e91315f794","level":1,"asc":1,"high":"12738.337","low":"12691.111","beginTime":1447996680,"endTime":1447997040,"childWave":[]},
{"id":"564eaed85ad5b","pid":"564e91315f794","level":1,"asc":0,"high":"12738.337","low":"12720.904","beginTime":1447997040,"endTime":1447997280,"childWave":[]},
{"id":"564eafc46c2f5","pid":"564e91315f794","level":1,"asc":1,"high":"12738.618","low":"12720.904","beginTime":1447997280,"endTime":1447997760,"childWave":[]}]}';

$s[] = '{"id":"564eb3fd6e71f","pid":null,"level":2,"asc":0,"high":"12738.618","low":"12687.849","beginTime":1447997760,"endTime":1447998060,"childWave":
[{"id":"564eb186b8239","pid":"564eb3fd6e71f","level":1,"asc":0,"high":"12738.618","low":"12687.849","beginTime":1447997760,"endTime":1447998060,"childWave":[]},
{"id":"564eb2d1de37a","pid":"564eb3fd6e71f","level":1,"asc":1,"high":"12711.598","low":"12687.849","beginTime":1447998060,"endTime":1447998360,"childWave":[]},
{"id":"564eb3fd6e771","pid":"564eb3fd6e71f","level":1,"asc":0,"high":"12711.598","low":"12709.844","beginTime":1447998360,"endTime":1447998480,"childWave":[]}]}';

$s[] = '{"id":"564eb459a0a33","pid":null,"level":2,"asc":1,"high":"12764.847","low":"12687.849","beginTime":1447998060,"endTime":1447998720,"childWave":
[{"id":"564eb459a0b5e","pid":"564eb459a0a33","level":1,"asc":1,"high":"12764.847","low":"12709.844","beginTime":1447998480,"endTime":1447998720,"childWave":[]}]}';

$s[] = '{"id":"564eb6ce4c811","pid":"564eb9066627e","level":2,"asc":0,"high":"12764.847","low":"12702.843","beginTime":1447998720,"endTime":1447999380,"childWave":
[{"id":"564eb58309ba4","pid":"564eb6ce4c811","level":1,"asc":0,"high":"12764.847","low":"12740.961","beginTime":1447998720,"endTime":1447998960,"childWave":[]},
{"id":"564eb6568201c","pid":"564eb6ce4c811","level":1,"asc":1,"high":"12746.307","low":"12740.961","beginTime":1447998960,"endTime":1447999080,"childWave":[]},
{"id":"564eb6ce4c864","pid":"564eb6ce4c811","level":1,"asc":0,"high":"12746.307","low":"12702.843","beginTime":1447999080,"endTime":1447999380,"childWave":[]},
{"id":"564eb819d9fc9","pid":"564eb6ce4c811","level":1,"asc":1,"high":"12713.333","low":"12702.843","beginTime":1447999380,"endTime":1447999620,"childWave":[]},
{"id":"564eb8cb84ec2","pid":"564eb6ce4c811","level":1,"asc":0,"high":"12713.333","low":"12704.731","beginTime":1447999620,"endTime":1447999800,"childWave":[]}]}';

$s[] = '{"id":"564eb9813b033","pid":"564ebcaa2d4b6","level":2,"asc":1,"high":"12743.716","low":"12702.843","beginTime":1447999380,"endTime":1448000160,"childWave":
[{"id":"564eb9813b10c","pid":"564eb9813b033","level":1,"asc":1,"high":"12743.716","low":"12704.731","beginTime":1447999800,"endTime":1448000160,"childWave":[]}]}';

// $s[] = '{"id":"564ebcaa2eeb9","pid":"564ebecf17603","level":2,"asc":0,"high":"12743.716","low":"12618.837","beginTime":1448000160,"endTime":1448001600,"childWave":
// [{"id":"564ebae88ccb1","pid":"564ebcaa2eeb9","level":1,"asc":0,"high":"12743.716","low":"12730.690","beginTime":1448000160,"endTime":1448000400,"childWave":[]},
// {"id":"564ebbf52b387","pid":"564ebcaa2eeb9","level":1,"asc":1,"high":"12734.712","low":"12730.690","beginTime":1448000400,"endTime":1448000580,"childWave":[]},
// {"id":"564ebcaa2ef09","pid":"564ebcaa2eeb9","level":1,"asc":0,"high":"12734.712","low":"12678.903","beginTime":1448000580,"endTime":1448000940,"childWave":[]},
// {"id":"564ebe13557d4","pid":"564ebcaa2eeb9","level":1,"asc":1,"high":"12699.280","low":"12678.903","beginTime":1448000940,"endTime":1448001180,"childWave":[]},
// {"id":"564ebf019f217","pid":"564ebcaa2eeb9","level":1,"asc":0,"high":"12699.280","low":"12618.837","beginTime":1448001180,"endTime":1448001600,"childWave":[]},
// {"id":"564ec089b3b43","pid":"564ebcaa2eeb9","level":1,"asc":1,"high":"12665.124","low":"12618.837","beginTime":1448001600,"endTime":1448001840,"childWave":[]},
// {"id":"564ec1776201a","pid":"564ebcaa2eeb9","level":1,"asc":0,"high":"12665.124","low":"12639.228","beginTime":1448001840,"endTime":1448002080,"childWave":[]}]}';

$c = min($c, count($s));
for ($i = 0; $i < $c; $i++) {
	saveWave($gw, json_decode($s[$i]));
}

echo json_encode($gw);

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