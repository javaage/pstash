<?php
require '../header.php';
require '../common.php';

$n = $_REQUEST["n"];
$t = $_REQUEST["t"];
if(empty($n)){
	$n = 1;
}
if(empty($t)){
	$t = 0;
}
$sql = "select sh.rownum,sh.current as dex, sh.clmn as clmn, (sz.current -sh.current + (SELECT avg(current) from indexrecord where code='sh000001' order by id desc)) as strong,sz.time as t from (SELECT id, code,current * 1.3437 as current,time FROM indexrecord WHERE code='sz399006') sz inner join (select i2.rownum,i1.code,i1.current,i1.time,(case when i2.clmn-i1.clmn > 0 then i2.clmn - i1.clmn else i2.clmn end) as clmn from (SELECT i.code,i.current,i.time,i.clmn,@rownum1:=@rownum1+1 as rownum FROM (select @rownum1:=0) a,indexrecord i WHERE code='sh000001' order by id desc) i1 inner join (SELECT i.code,i.current,i.time,i.clmn,@rownum2:=@rownum2+1 as rownum FROM (select @rownum2:=0) a,indexrecord i WHERE code='sh000001' order by id desc) i2 on i1.rownum = i2.rownum + 1) sh on sz.time = sh.time where sh.rownum%$n=0 and sh.time > $t ORDER by t desc limit 240";

$result = $mysql -> query($sql);
$strongs = array();
while ($mr = $result -> fetch_array(MYSQLI_ASSOC)) {
	$mr['time'] = date('d H:i',$mr['t']);
	$strongs[] = $mr;
}
echo json_encode($strongs);
mysqli_free_result($result);

$mysql -> close();
?>