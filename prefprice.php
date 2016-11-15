<?php
	require 'header.php';
	require 'common.php';
    $code = $_REQUEST["code"];
    $pref = prefPrice($code);
	echo json_encode($pref);
?>