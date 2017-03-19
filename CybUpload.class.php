<?php
/**
 * Created by PhpStorm.
 * User: cyb
 * Date: 2017/3/16
 * Time: 14:45
 * version：1.0    (已完成)
 *
 * 描述：文件上传类
 *  1.单文件上传
 *  2.可修改文件上传大小限制以及文件类型限制
 *  2.可以设置文件名保持原名称或者带时间的随机名称
 *  3.可以设置上传文件保存在一个文件夹中或者保存在有年月日的文件夹树中（必须是服务器中）
 *  4.可以打印上传文件的各种信息
 *  5.可以返回上传文件成功上传后的完整路径，以便数据库保存文件数据
 *
 * 欠缺：
 *  1.多文件上传并未实现
 *  3.错误处理功能未实现
 *  2.图片缩略图并未实现
 */
class CybUpload{
//    属性
//    private static $conn = false;    //    实例化类的标识符
    private $originName;        //上传的文件名称
    private $fileTypeAll;      //上传的文件类型
    private $lastName;          //文件后缀名
    private $tmpName;           //临时文件名称
    private $error;             //错误编号
    private $fileSize;         //上传文件的大小
    private $isRand = true;    //判断是否随机
    private $fileSaveDir;     //新文件保存目录设置

    private $fileName;          //新文件名称
    private $fullFileDir;       //最终保存新文件的完整路径
    private $allowType =array('gif','jpg','jpeg','png');         //允许上传的文件类型
    private $maxSize = 5242880;           //允许上传的最大文件大小 1024*1024*5 bit
    private $errorMsg;          //错误信息

//    构造函数 $file 为文件上传域
    public function __construct($file){
        $this->originName = $_FILES[$file]["name"];
        $this->fileTypeAll = $_FILES[$file]["type"];
        $this->tmpName = $_FILES[$file]["tmp_name"];
        $this->error = $_FILES[$file]["error"];
        $this->fileSize = $_FILES[$file]["size"];
//        获得文件后缀名
        $this->getExtensionName();

//      错误处理
        $this->errorMsg();
    }



//    设置能上传的最大值
    public function setMaxSize($max){
        $this->maxSize = $max;
    }

//    设置能上传的类型
    public function setFileType($type=array()){
        $this->allowType = $type;
    }


//    上传文件处理函数
    public function uploadFile($fileRootDir,$isRandName =true,$isDateDir =true){
//        转化文件路径中的'/'和'\'为 DIRECTORY_SEPARATOR
            $fileRootDir = str_replace("/",DIRECTORY_SEPARATOR,$fileRootDir);
            $fileRootDir = str_replace("\\",DIRECTORY_SEPARATOR,$fileRootDir);
            $this->fileSaveDir = $fileRootDir;

//        判断文件上传类型是否正确
         $this->checkType();
//        判断文件上传大小是否符合要求
         $this->checkSize();
//        给文件取名(使用原名还是新名称)
         $this->setNewName($isRandName);
//        保存新文件到服务器(保存在一个文件夹中还是按年月日保存)
         $this->saveFile($isDateDir);
    }

//    保存新文件函数
    private function saveFile($isDateDir){
        if(!($this->dirIsExist($this->fileSaveDir))){
            $this->mkNewDir($this->fileSaveDir);
        }
        if($isDateDir){
//            以年月日的文件树形式保存文件
            $fileDir = $this->getDateDir();
        }else{
//            直接保存文件到文件根目录中的下级某个文件目录
            $fileDir = $this->fileSaveDir;
        }
        $this->fullFileDir = $fileDir.$this->fileName;
//        保存文件
        move_uploaded_file($this->tmpName,$this->fullFileDir);

    }

//    获得以年月日命名的文件夹树
    private function getDateDir(){
//        拼接年文件夹
        $dirstr = $this->fileSaveDir.date("Y").DIRECTORY_SEPARATOR;
        if(!($this->dirIsExist($dirstr))){
            $this->mkNewDir($dirstr);  //根/年/
        }
//        拼接月文件夹
        $dirstr .=date("m").DIRECTORY_SEPARATOR;
        if(!($this->dirIsExist($dirstr))){
            $this->mkNewDir($dirstr);  //根/年/月/
        }
//        拼接日文件夹
        $dirstr .=date("d").DIRECTORY_SEPARATOR;
        if(!($this->dirIsExist($dirstr))){
            $this->mkNewDir($dirstr);  //根/年/月/日/
        }
        return $dirstr;
    }

//    判断目录是否存在
    private function dirIsExist($dir){
        return file_exists($dir);
    }

//    创建新目录
    private function mkNewDir($dir){
        return mkdir($dir);
    }


//    检查文件上传的类型是否符合要求
    private function checkType(){
        if(!in_array($this->lastName,$this->allowType)){
            $this->error = "Typeerror";
            echo "文件 <strong> {$this->originName} </strong> 类型不符合，请上传类型为".implode(',',$this->allowType)."的文件";
            die;
        }
    }

//    检查文件大小是否符合要求
    private function checkSize(){
        if($this->fileSize > $this->maxSize){
            $this->error = "Typeerror";
            echo "文件 <strong> {$this->originName} </strong> 过大，请把文件大小控制在".($this->maxSize/1024/1024)."Mb";
            die;
        }
    }

//    给上传的文件取名
    private function setNewName($isRand = true){

        $this->isRand = $isRand;
        if($this->isRand){
//            取随机数名称：5位随机数+上传日期时间组合
            $this->fileName = $this->setRandName().".".$this->lastName;
            echo $this->fileName;
//            die;
        }else{
//            让新文件名称等于上传文件名称
            $this->fileName = $this->originName.".".$this->lastName;
            echo $this->fileName;
        }
    }

//    随机命名函数
    private function setRandName(){
        date_default_timezone_set('PRC'); //设置中国时区
        $str = rand(100000,999999)."_".date("YmdHis",time());
        return $str;
    }

//    获得文件后缀名函数
    private function getExtensionName(){
        $this->lastName = substr($this->originName,(strrpos($this->originName,'.'))+1);
    }

//    得到上传成功的文件的完整路径
    public function getFullDir(){
//        if(!$this->fullFileDir){
//            echo "文件还未上传，无法得到新文件完整路径";
//            die;
//        }
        return $this->fullFileDir;
    }

//    得到上传文件的信息
    public function getFileMsg(){
        echo "<pre>";
        echo "上传文件信息：<br>";
        echo "<br>文件名称：".$this->originName;
        echo "<br>文件后缀名：".$this->lastName;
        echo "<br>文件完整类型：".$this->fileTypeAll;
        echo "<br>文件大小：".$this->fileSize;
        echo "<br>临时文件路径：".$this->tmpName;
        echo "<br>文件路径：".__FILE__;
        echo "</pre>";
    }


//    错误处理函数
    private function errorMsg(){
        $str = "上传文件 <strong> {$this->originName} </strong> 出错：";
        switch($this->error){
            case 0:
                $str = "文件 <strong> {$this->originName} </strong> 上传成功";
                break;
            case "Typeerror":
                $str .= "文件 <strong> {$this->originName} </strong> 不符合要求，请上传类型为".implode(',',$this->allowType)."的文件";
                break;
            default:
                $str = "出现未知错误！！！";

        }
//        return $str;
        $this->errorMsg = $str;
    }







//    公共静态函数，用于实例化类对象
//    public static  function getInstance($file){
//        if(self::$conn ==false){
//            self::$conn = new self;
//        }
//        $this->file = $file;
//        return self::$conn;
//    }




}
