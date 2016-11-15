<?php
require 'header.php';
$lm = date("Y-m-d", strtotime("-8 days"));
$lm5 = date("Y-m-d", strtotime("-5 days"));
$strQuery = "DELETE FROM cand_rate where time < '$lm' LIMIT 80000";
$mysql -> query($strQuery);
if ($mysql -> error != 0) {
	die("Error:" . $mysql -> errmsg());
}
$strQuery = "DELETE FROM cand_trans where time < '$lm' LIMIT 80000";
$mysql -> query($strQuery);
if ($mysql -> error != 0) {
	die("Error:" . $mysql -> errmsg());
}
$strQuery = "DELETE FROM stockrecord where date < '$lm5' LIMIT 80000";
$mysql -> query($strQuery);
if ($mysql -> error != 0) {
	die("Error:" . $mysql -> errmsg());
}
$strQuery = "DELETE FROM bkrecord where date < '$lm' LIMIT 80000";
$mysql -> query($strQuery);
if ($mysql -> error != 0) {
	die("Error:" . $mysql -> errmsg());
}
$strQuery = "DELETE FROM bk_trans where time < '$lm' LIMIT 80000";
$mysql -> query($strQuery);
if ($mysql -> error != 0) {
	die("Error:" . $mysql -> errmsg());
}
$strQuery = "DELETE FROM crecord where date < '$lm' LIMIT 80000";
$mysql -> query($strQuery);
if ($mysql -> error != 0) {
	die("Error:" . $mysql -> errmsg());
}
$strQuery = "DELETE FROM c_trans where time < '$lm' LIMIT 80000";
$mysql -> query($strQuery);
if ($mysql -> error != 0) {
	die("Error:" . $mysql -> errmsg());
}
$strQuery = "DELETE FROM director where time < '$lm' LIMIT 80000";
$mysql -> query($strQuery);
if ($mysql -> error != 0) {
	die("Error:" . $mysql -> errmsg());
}
$strQuery = "DELETE FROM stockaction where time < '$lm' LIMIT 80000";
$mysql -> query($strQuery);
if ($mysql -> error != 0) {
	die("Error:" . $mysql -> errmsg());
}
$strQuery = "DELETE FROM waverecord where dt < '$lm' LIMIT 80000";
$mysql -> query($strQuery);
if ($mysql -> error != 0) {
	die("Error:" . $mysql -> errmsg());
}

$sql = "select preflist from candidate order by id desc limit 1";
$result = $mysql -> query($sql);
$row = $result -> fetch();
$strCodes = str_replace(",", "','", $row[0]);

$sqlDelete = "DELETE FROM attend WHERE code not in ('$strCodes')";
$mysql -> query($sqlDelete);
if ($mysql -> error != 0) {
	die("Error:" . $mysql -> errmsg());
}

$sqlDelete = "DELETE FROM cand_rate WHERE  code not in ('$strCodes')";
$mysql -> query($sqlDelete);
if ($mysql -> error != 0) {
	die("Error:" . $mysql -> errmsg());
}

$sqlDelete = "DELETE FROM cand_trans WHERE  code not in ('$strCodes')";
$mysql -> query($sqlDelete);
if ($mysql -> error != 0) {
	die("Error:" . $mysql -> errmsg());
}

$sqlDelete = "DELETE FROM director WHERE  code not in ('$strCodes')";
$mysql -> query($sqlDelete);
if ($mysql -> error != 0) {
	die("Error:" . $mysql -> errmsg());
}

$sqlDelete = "DELETE FROM holder WHERE  code not in ('$strCodes')";
$mysql -> query($sqlDelete);
if ($mysql -> error != 0) {
	die("Error:" . $mysql -> errmsg());
}

$sqlDelete = "DELETE FROM sign WHERE  code not in ('$strCodes')";
$mysql -> query($sqlDelete);
if ($mysql -> error != 0) {
	die("Error:" . $mysql -> errmsg());
}

$sqlDelete = "DELETE FROM stockrecord WHERE  code not in ('$strCodes')";
$mysql -> query($sqlDelete);
if ($mysql -> error != 0) {
	die("Error:" . $mysql -> errmsg());
}

$sqlDelete = "DELETE FROM waverecord WHERE  code not in ('$strCodes')";
$mysql -> query($sqlDelete);
if ($mysql -> error != 0) {
	die("Error:" . $mysql -> errmsg());
}

$sql = "SELECT DISTINCT w.code from waverecord w LEFT join sign s on w.code = s.code WHERE (LEFT(w.code,2)='sh' or LEFT(w.code,2)='sz') and w.code <> '$icode' and s.code is null";

$result = $mysql -> query($sql);

$arr = array();

while ($row = $result -> fetch()) {
	$code = $row[0];
	$arr[] = "('$code')";
}

$sqlInserts = "insert into sign (code) values " . join(",", $arr);
$mysql -> query($sqlInserts);

if ($mysql -> error != 0) {
	die("Error:" . $mysql -> errmsg());
}

$mysql -> close();
?>