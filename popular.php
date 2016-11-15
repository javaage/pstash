<?php
require 'header.php';
require 'common.php';
$n = $_REQUEST["n"];
if(empty($n) || $n < 1){
	$n = 60;
}
$sql = "select s.time,s.strong,s.dex from (SELECT id,substring(time,12,5) as time,strong,dex FROM stockaction order by id desc limit $n) s order by id ";

$result = $mysql -> query($sql);
$strongs = array();
while ($mr = $result -> fetch_array(MYSQLI_ASSOC)) {
	$strongs[] = $mr;
}
echo json_encode($strongs);
mysqli_free_result($result);

$mysql -> close();
?>