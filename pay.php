<?php 
require 'header.php';
$orderId = $_REQUEST['order_id'];
$appId = $_REQUEST['app_id'];
$userId = $_REQUEST['user_id'];
$payType = $_REQUEST['pay_type'];

$resultCode = $_REQUEST['result_code'];
$resultString = $_REQUEST['result_string'];
$tradeId = $_REQUEST['trade_id'];
$amount = $_REQUEST['amount'];
$payTime = date ( 'Y-m-d H:i:s' );
$sign = $_REQUEST['sign'];

$sql = "INSERT INTO pay_result (orderId, appId, userId, payType, resultCode, resultString, tradeId, amount, payTime, sign, comment) VALUES ('$orderId', '$appId', '$userId', $payType, $resultCode, '$resultString', '$tradeId', $amount, '$payTime', '$sign', '')";
$mmc = memcache_init ();
$mysql->query ( $sql );
if ($mysql->error != 0) {
	die ( "Error:" . $mysql->errmsg () );
}
$mysql->close ();

echo "failed";

/**
 * �����ʵ�ip�Ƿ�Ϊ�涨�������ip
 * Enter description here ...
 */
function check_ip(){
	$ALLOWED_IP=array('219.234.85.205',
'219.234.85.206',
'219.234.85.207',
'219.234.85.217',
'219.234.85.234');
	$IP=getIP();
	$check_ip_arr= explode('.',$IP);//Ҫ����ip��ֳ�����
	#����IP
	if(!in_array($IP,$ALLOWED_IP)) {
		foreach ($ALLOWED_IP as $val){
			if(strpos($val,'*')!==false){//������*�������
				$arr=array();//
				$arr=explode('.', $val);
				$bl=true;//���ڼ�¼ѭ��������Ƿ���ƥ��ɹ���
				for($i=0;$i<4;$i++){
					if($arr[$i]!='*'){//������* ��Ҫ������⣬���Ϊ*����������Ͳ����
						if($arr[$i]!=$check_ip_arr[$i]){
							$bl=false;
							break;//��ֹ��鱾��ip ���������һ��ip
						}
					}
				}//end for
				if($bl){//�����true���ҵ���һ��ƥ��ɹ��ľͷ���
					return;
				}
			}
		}//end foreach
		header('HTTP/1.1 403 Forbidden');
		echo "Access forbidden";
		die;
	}
}
/*
* ��÷��ʵ�IP
* Enter description here ...
*/
function getIP() {
	return isset($_SERVER["HTTP_X_FORWARDED_FOR"])?$_SERVER["HTTP_X_FORWARDED_FOR"]
	:(isset($_SERVER["HTTP_CLIENT_IP"])?$_SERVER["HTTP_CLIENT_IP"]
			:$_SERVER["REMOTE_ADDR"]);
}

?>