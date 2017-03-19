<?php
/**
 * Created by PhpStorm.
 * User: hans
 * Date: 2017/3/16
 * Time: 14:44
 */
header('content-type:text/html;charset=utf-8');
//加载上传文件类
include_once "CybUpload.class.php";


//$arr = array(
//    "file" =>array(3,"a","34"),
//    "file1" =>array(3,"a","34"),
//    "file2" =>array(3,"a","34")
//);
//echo count($arr);

//如果接收到上传的文件则进入文件处理
if(!empty($_FILES) && $_FILES["file"]["error"] == 0){
//    实例化上传类对象
    $upload = new CybUpload("file");

//    处理上传文件
    $upload->uploadFile("H:\object\wwwroot\php_libs_class\upload/");

//    打印上传文件信息
    $upload->getFileMsg();

//   上传文件路径(可以保存到数据库)
    echo $upload->getFullDir();

}

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title></title>

</head>
<body>
    <form action="#" method="post" enctype="multipart/form-data">
        <h1>我要上传文件</h1>
        选择文件：<input type="file" name="file">
        <br>
        <input type="submit" value="上传文件">
    </form>
</body>
</html>