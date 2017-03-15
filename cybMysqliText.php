<?php

/**
 * Created by PhpStorm.
 * User: hans
 * Date: 2017/3/15
 * Time: 14:20
 */
header('content-type:text/html;charset=utf-8');
include "CybMysqli.class.php";

$arr = array(
    "title" =>"h3趣味体育",
    "description" => '原标题：“肌肉图会玩”'
);

$arr1 = array('id'=>array(11,12,13));


//实例化对象
$mysql = CybMysqli::getInstance();

//查询单条数据
//$row = $mysql->getOne("select * from news ORDER BY id desc");
//print_r($row);

//查询多条
//$data = $mysql->getAll("select * from news");
//print_r($data);

//插入数据
//$n = $mysql->insert("news",$arr);
//echo $n;

//修改数据
//$n = $mysql->update("news",$arr,"id=2");
//echo $n;

//删除单条数据
//$n = $mysql->deleteOne('news',$arr1);
//echo $n;

//删除单条数据
//$n = $mysql->deleteAll('news',$arr1);
//echo $n;

//查看连接数据库使用的字符集
//$mysql->return_encoding();

//查看数据库系统中的数据库
//$db = $mysql->showDbs();
//$mysql->pr($db);

//查看当前数据库中的表
//$db = $mysql->showTabs();
//$mysql->pr($db);

//返回mysql服务器信息
//$mes = $mysql->showMysqlServerMessage();
//$mysql->pr($mes);

//返回客户端信息
$mes = $mysql->showClientMessage();
$mysql->pr($mes);


