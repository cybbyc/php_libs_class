<?php
header("content-type:text/html;charset=utf-8");

include "mysql.class.php"; 

// ����mysql����
$mysql = new Mysql("localhost","root","","test");

// �����µ����ݿ�
// $mysql->create_database("cyb");


// ��дsql���
$mysql->query("select *from news");

// ��ý����1
$data = $mysql->fetch_array();
print_r($data);

// ��ý����2
$data = $mysql->fetch_assoc();
print_r($data);

// ��ý����3
$data = $mysql->fetch_row();
print_r($data);

// ��ý����4
$data = $mysql->fetch_Object();
print_r($data);

