<?php 
header("Content-Type", "application/x-www-form-urlencoded; charset=utf8");
require 'header.php';

$sqlIndex = "SELECT ver,bcheck,title,content,url,type,extra FROM version ORDER by id desc LIMIT 1";

$tResult = $mysql->query($sqlIndex);

$row=mysqli_fetch_assoc($tResult);
if(isset($row)){
	echo json_encode($row);
}
mysqli_free_result($tResult); 
$mysql->close();
?>