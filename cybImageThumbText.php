<?php
/**
 * Created by PhpStorm.
 * User: cyb
 * Date: 2017/3/18
 * Time: 11:14
 */
header('content-type:text/html;charset=utf-8');
include_once "CybImageThumb.class.php";

//实例化缩略图类
$thumb = new CybThumb("E:/image/8.jpg");

//设置缩略图保存位置
$thumb->setThumbDir("E:\cyb\wamproot\php_libs_class/upload/",true);

//生成百分比缩略图，参数为百分比
$thumb->createThumbPercent("0.5");

//打印原图信息
$thumb->showOriginImageMsg();






