<?php
require 'header.php';
require 'common.php';

print_r(get_extension_funcs("xml"));
print_r(get_extension_funcs("cron"));

$strQuery = "insert into attend(code) values('sz399001')";
echo $strQuery;
$count = $mysql->exec ( $strQuery );
echo $count;