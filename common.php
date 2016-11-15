<?php
class Wave {
	var $id = "";
	var $pid = "";
	var $level = 0;
	var $asc = 0;
	var $high = 0;
	var $low = 0;
	var $beginTime = 0;
	var $endTime = 0;
	var $count = 0;
	var $childWave = array();
}

class Pref {
	var $prefBuy = 0;
	var $prefSell = 0;
	var $current = 0;
	var $high = 0;
	var $low = 0;
	var $concept = 0;
}

function getUrl() {
	global $kv, $mysql, $icode;
	$ct = date('Y-m-d');
	$urls = $kv -> get($ct);

	if (empty($urls)) {
		$sql = "SELECT DISTINCT code from waverecord WHERE (LEFT(code,2)='sh' or LEFT(code,2)='sz') and code not in ('sh000001','sz399001','399006') and instr(lower((select preflist from candidate order by id desc limit 1)),code) > 0";
		$result = $mysql -> query($sql);
		$baseUrl = "http://hq.sinajs.cn/list=";
		$maxCount = 436;
		$counter = 0;
		$arr = array();
		$urls = array();
		while ($row = $result -> fetch()) {

			$code = $row[0];
			$urlQuery = $baseUrl . $code;
			$html = file_get_contents($urlQuery);
			$html = str_replace("\"", "", $html);
			$items = explode(',', $html);
			if (isset($items[1]) && $items[1] != 0) {
				array_push($arr, $code);
				$counter++;
				if ($counter >= $maxCount) {
					array_push($urls, $baseUrl . join(",", $arr));
					$arr = array();
					$counter = 0;
				}
			}

		}
		if ($counter > 0) {
			array_push($urls, $baseUrl . join(",", $arr));
		}
		$kv -> set($ct, $urls);
	}
	return $urls;
}

function saveWave(&$g, $w) {
	if (empty($g)) {
		$g = json_decode(json_encode($w));
	} else {
		if ($g -> level == $w -> level) {
			if ($w -> asc) {
				if ($w -> high > $g -> high) {
					saveGlobal($g);
					$g = json_decode(json_encode($w));
				} else {
					$c = json_decode(json_encode($g));
					$g -> id = uniqid();
					$g -> level = $g -> level + 1;
					$g -> childWave = array();
					$c -> pid = $g -> id;
					$g -> childWave[] = $c;
					saveHistory($g);
					$w -> pid = $g -> id;
					$g -> childWave[] = $w;
				}
			} else {
				if ($w -> low < $g -> low) {
					saveGlobal($g);
					$g = json_decode(json_encode($w));
				} else {
					$c = json_decode(json_encode($g));
					$g -> id = uniqid();
					$g -> level = $g -> level + 1;
					$g -> childWave = array();
					$c -> pid = $g -> id;
					$g -> childWave[] = $c;
					saveHistory($g);
					$w -> pid = $g -> id;
					$g -> childWave[] = $w;
				}
			}
		} else if ($g -> level > $w -> level) {
			$node = $g;
			$parentNode = null;
			while ($node -> level > $w -> level + 1) {
				if (empty($node -> childWave)) {
					$parentNode = $node;
					$node = json_decode(json_encode($node));
					$node -> id = uniqid();
					$node -> level = $parentNode -> level - 1;
					$node -> childWave = array();
					$node -> pid = $parentNode -> id;
					$parentNode -> childWave[] = $node;
				} else {
					$parentNode = $node;
					$node = $node -> childWave[count($node -> childWave) - 1];
				}
			}

			if ($w -> asc) {
				if ($node -> asc) {
					if ($w -> high >= $node -> high) {// ��������
						saveHistory($node);
						$w -> pid = $node -> id;
						$node -> childWave[] = $w;
						$node -> high = $w -> high;
						$node -> endTime = $w -> endTime;
						while (!empty($parentNode)) {
							if ($parentNode -> asc) {
								if ($w -> high > $parentNode -> high) {
									$parentNode -> high = $w -> high;
									$parentNode -> endTime = $w -> endTime;
									$parentNode = findNodeById($g, $parentNode -> pid);
								} else {
									break;
								}
							} else {
								$node = array_pop($parentNode -> childWave);
								saveWave($g, $node);
								break;
							}
						}
					} else {// ��ת����
						if (!empty($parentNode)) {
							$node = array_pop($parentNode -> childWave);
							saveWave($g, $node);
						}
						$temp = array_pop($node -> childWave);
						$nw = new Wave();
						$nw -> id = uniqid();
						$nw -> level = $node -> level;
						$nw -> asc = 1 - $node -> asc;
						if (empty($temp)) {
							$nw -> high = $w -> high;
							$nw -> low = $w -> low;
							$nw -> beginTime = $w -> beginTime;
							$nw -> endTime = $w -> endTime;
						} else {
							$nw -> high = $temp -> high;
							$nw -> low = $temp -> low;
							$nw -> beginTime = $temp -> beginTime;
							$nw -> endTime = $temp -> endTime;
							$temp -> pid = $nw -> id;
							$nw -> childWave[] = $temp;
						}
						saveHistory($nw);
						$w -> pid = $nw -> id;
						$nw -> childWave[] = $w;
						$nw -> endTime = $w -> endTime;
						saveWave($g, $nw);
					}
				} else {// ����������
					if ($w -> high >= $node -> high) {
						if (!empty($parentNode)) {
							$node = array_pop($parentNode -> childWave);
							saveWave($g, $node);
						}
						$nw = json_decode(json_encode($w));
						$nw -> childWave = array();
						$nw -> id = uniqid();
						$nw -> level = $w -> level + 1;
						$w -> pid = $nw -> id;
						$nw -> childWave[] = $w;
						saveWave($g, $nw);
					} else {
						saveHistory($node);
						$w -> pid = $node -> id;
						$node -> childWave[] = $w;
					}
				}
			} else {// ��������
				if (!$node -> asc) {// ��������
					if ($w -> low <= $node -> low) {// ��������
						saveHistory($node);
						$w -> pid = $node -> id;
						$node -> childWave[] = $w;
						$node -> low = $w -> low;
						$node -> endTime = $w -> endTime;

						while (!empty($parentNode)) {
							if (!$parentNode -> asc) {
								if ($w -> low < $parentNode -> low) {
									$parentNode -> low = $w -> low;
									$parentNode -> endTime = $w -> endTime;
									$parentNode = findNodeById($g, $parentNode -> pid);
								} else {
									break;
								}
							} else {
								$node = array_pop($parentNode -> childWave);
								saveWave($g, $node);
								break;
							}
						}
					} else {// ��ת����
						if (!empty($parentNode)) {
							$node = array_pop($parentNode -> childWave);
							saveWave($g, $node);
						}
						$temp = array_pop($node -> childWave);
						$nw = new Wave();
						$nw -> id = uniqid();
						$nw -> level = $node -> level;
						$nw -> asc = 1 - $node -> asc;
						if (empty($temp)) {
							$nw -> high = $w -> high;
							$nw -> low = $w -> low;
							$nw -> beginTime = $w -> beginTime;
							$nw -> endTime = $w -> endTime;
						} else {
							$nw -> high = $temp -> high;
							$nw -> low = $temp -> low;
							$nw -> beginTime = $temp -> beginTime;
							$nw -> endTime = $temp -> endTime;
							$temp -> pid = $nw -> id;
							$nw -> childWave[] = $temp;
						}
						saveHistory($nw);
						$w -> pid = $nw -> id;
						$nw -> childWave[] = $w;
						saveWave($g, $nw);
					}
				} else {// ����������
					if ($w -> low <= $node -> low) {
						if (!empty($parentNode)) {
							$node = array_pop($parentNode -> childWave);
							saveWave($g, $node);
						}
						$nw = json_decode(json_encode($w));
						$nw -> id = uniqid();
						$nw -> level = $w -> level + 1;
						$w -> pid = $nw -> id;
						$nw -> childWave = array();
						$nw -> childWave[] = $w;
						saveWave($g, $nw);
					} else {
						saveHistory($node);

						$w -> pid = $node -> id;
						$node -> childWave[] = $w;
					}
				}
			}
		}
	}
}

function findNodeById($node, $id) {

	if ($node -> id == $id) {
		return $node;
	} else {
		$rnode = null;
		foreach ($node->childWave as $sn) {
			$rnode = findNodeById($sn, $id);
			if (!empty($rnode)) {
				return $rnode;
			}
		}
	}
	return null;
}

function transfer(&$w) {
	$w -> beginTime = date("Y-m-d H:i:s", $w -> beginTime);
	$w -> endTime = date("Y-m-d H:i:s", $w -> endTime);
	foreach ($w->childWave as $cw) {
		transfer($cw);
	}
}

function getArrow($gw) {
	if (empty($gw)) {
		return '';
	} else {
		while ($gw -> level > 9) {
			$cn = count($gw -> childWave);
			$gw = $gw -> childWave[$cn - 1];
		}

		$r = '';
		while ($gw -> level > 3) {
			$cn = count($gw -> childWave);
			$r = $r . $cn . $gw -> asc;
			$gw = $gw -> childWave[$cn - 1];
		}
		return $r;
	}
}

function countArrow($gw) {
	if (empty($gw)) {
		return 0;
	} else {
		while ($gw -> level > 9) {
			$cn = count($gw -> childWave);
			$gw = $gw -> childWave[$cn - 1];
		}
		//return json_encode($gw);
		$r = 0;
		while ($gw -> level > 3) {
			$r += $gw -> asc;
			$cn = count($gw -> childWave);
			$gw = $gw -> childWave[$cn - 1];
		}
		return $r;
	}
}

function popular($fTime, $lTime, $asc) {
	global $mysql, $kv;
	$strUpdate = "select (l.current - f.current)/l.close as main  from (SELECT code,name,close,current FROM `indexrecord` WHERE (time >= " . $fTime . " and time < " . ($fTime + 60) . ") and code = 'sh000001') f LEFT JOIN (SELECT code,name,close,current FROM indexrecord WHERE (time > " . ($lTime - 60) . " AND time <= " . $lTime . ") and code = 'sh000001') l ON f.code = l.code";
	$result = $mysql -> query($strUpdate);
	$main = $result -> fetch();
	$strUpdate = "select (l.current - f.current)/l.close as main  from (SELECT code,name,close,current FROM `indexrecord` WHERE (time >= " . $fTime . " and time < " . ($fTime + 60) . ") and code = 'sz399006') f LEFT JOIN (SELECT code,name,close,current FROM indexrecord WHERE (time > " . ($lTime - 60) . " AND time <= " . $lTime . ") and code = 'sz399006') l ON f.code = l.code";
	$result = $mysql -> query($strUpdate);
	$concept = $result -> fetch();
	echo $main[0] . "</br>";
	echo $concept[0] . "</br>";
	return 400 * ($concept[0]-$main[0]);
}

function prefPrice($code){
	global $mysql, $kv;
	
	$time = time();    
    $ct = date('Y-m-d');
    $deltaTime = $time - strtotime($ct);
    $calTime = $time;
    $baseUrl = "http://hq.sinajs.cn/list=";
    $urlQuery = $baseUrl . $code;
    $html = file_get_contents($urlQuery);
    $html = str_replace("\"","",$html);
    $items = explode(',',$html);
    
    $todayCurrent = $items[8];
    
    $strQuery = "SELECT close,current, high, low, clmn FROM `stockrecord` WHERE code = '" . $code . "' and date < '" . $ct . "' and time - unix_timestamp(date) = $deltaTime " ;
	
    $result = $mysql->query($strQuery);
    $currentResult = array();
	if(empty($result)){
		return null;
	}else{
		while($mr = $result->fetch_array(MYSQLI_ASSOC)){
	    	array_push($currentResult,$mr);
	    }
	}
    mysqli_free_result($result);
    $cnt = count($currentResult);

    $totalClmn = 0;
    for($i = 0; $i < $cnt; $i++){
    	$totalClmn += $currentResult[$i]['clmn'];
    }

    if($cnt > 0){
    	$clmnRate = $items[8] * $cnt/$totalClmn;
    }else{
    	$clmnRate = 1;
    }
    
    $strQuery = "SELECT close,high,low,clmn,(SELECT group_concat(name) FROM `category` WHERE locate('$code',content)>1) as concept FROM `stockrecord` WHERE code = '" . $code . "' and date < '" . $ct . "' and time - unix_timestamp(date)= 53760 " ;

    $result = $mysql->query($strQuery);
    $endResult = array();
    while($mr = $result->fetch_array(MYSQLI_ASSOC)){
    	array_push($endResult,$mr);
    }
    mysqli_free_result($result);
    $totalClmn = 0;
    $totalWidth = 0;
    for($i = 0; $i < count($endResult); $i++){
    	$totalClmn += $endResult[$i]['clmn'];
    	$totalWidth += ($endResult[$i]['high'] - $endResult[$i]['low'])/$endResult[$i]['close'];
		$concept = $endResult[$i]['concept'];
    }
	
    $rateRange = $clmnRate * $totalWidth/count($endResult);
    $prefBuy = $items[4] - $rateRange * $items[2];
    $prefSell = $items[5] + $rateRange * $items[2];
    $top = $items[2] * 1.1;
    $bottom = $items[2] * 0.9;
    $currentPrice = $items[3];
    $high = $items[4];
    $low = $items[5];
    $prefBuy = $prefBuy > $bottom? $prefBuy : $bottom;
    $prefSell = $prefSell < $top? $prefSell : $top;
    $prefBuy = $prefBuy < $currentPrice? $prefBuy : $currentPrice;
    $prefSell = $prefSell > $currentPrice? $prefSell : $currentPrice;
    
    $pref = new Pref();
	$pref->prefBuy = $prefBuy;
	$pref->prefSell = $prefSell;
	$pref->current = $currentPrice;
	$pref->high = $high;
	$pref->low = $low;
	$pref->concept = $concept;
	return $pref;
}
