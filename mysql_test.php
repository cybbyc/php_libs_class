<?php
header("content-type:text/html;charset=utf-8");

include "mysql.class.php"; 

// 创建mysql对象
$mysql = new Mysql("localhost","root","","test");

// 创建新的数据库
// $mysql->create_database("cyb");


// 编写sql语句
$mysql->query("select *from news");

// 获得结果集1
$data = $mysql->fetch_array();
print_r($data);

// 获得结果集2
$data = $mysql->fetch_assoc();
print_r($data);

// 获得结果集3
$data = $mysql->fetch_row();
print_r($data);

// 获得结果集4
$data = $mysql->fetch_Object();
print_r($data);

