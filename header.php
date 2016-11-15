<?php
// 指定允许其他域名访问
header('Access-Control-Allow-Origin:*');
// 响应类型
header('Access-Control-Allow-Methods:*');
// 响应头设置
header('Access-Control-Allow-Headers:*');

$kv = new Redis ();
$kv->connect ( '10.72.6.23', 49895 );
$kv->auth("ab05a449-ffa0-449f-ab55-73fa2f7e4945");

//$mysql = pg_connect ( "host= dbname= user= password=" ) or die ( 'Could not connect: ' . pg_last_error () );

//pg_set_client_encoding($mysql, "utf8");
//

$mysql = new PDO("pgsql:dbname=dba40818f4855403588ff015a3fcfecd3;host=10.72.6.143", "uf704ee0c1f404e8cb52937553e2810d4", "53639f87bbf940e5b15d8fe45864e165"); 
$mysql->exec("set names utf8");
//$mysql->set_charset ( "utf8" );

$icode = 'sz399001';
$ycode = '399001.sz';
