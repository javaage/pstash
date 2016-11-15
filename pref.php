<?php
require 'header.php';
require 'common.php';

$sql = "SELECT s.code,a.name, s.sell, s.buy, s.prefBuy, s.prefSell, s.current, s.high, s.low, s.concept FROM sign s
INNER join allstock a
on s.code=a.code
where s.prefBuy > 0 order by s.current/s.prefBuy limit 30";

$result = $mysql -> query($sql);
$codes = array();
while ($mr = $result -> fetch_array(MYSQLI_ASSOC)) {
	array_push($codes, $mr);
}
echo json_encode($codes, JSON_UNESCAPED_UNICODE);

mysqli_free_result($result);

$mysql -> close();
?>