<?php
/**
 * Created by PhpStorm.
 * User: cyb
 * Date: 2017/3/16
 * Time: 14:45
 *
 * 描述：文件上传类（单文件上传）
 */
class CybUpload{

//    属性
//    private static $conn = false;    //    实例化类的标识符

//    private $file;              //上传文件域名称
    private $originName;        //上传的文件名称
    private $fileType;          //上传的文件类型
    private $tmpName;           //临时文件名称
    private $error;             //错误编号
    private $fileSize;          //上传文件的大小


    private $fileName;          //新文件名称
    private $allowType =array('gif','jpg','jpeg','png');         //允许上传的文件类型
    private $maxSize = 5242880;           //允许上传的最大文件大小 1024*1024*5 bit
    private $errorMsg;          //错误信息

//    构造函数 $file为文件上传域
    public function __construct($file){
        $this->originName = $_FILES[$file]["name"];
        $this->fileType = $_FILES[$file]["type"];
        $this->tmpName = $_FILES[$file]["tmp_name"];
        $this->error = $_FILES[$file]["error"];
        $this->fileSize = $_FILES[$file]["size"];
//      错误处理
        $this->errorMsg();
//       上传文件处理
        $this->uploadFile();

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
    private function uploadFile(){
//        判断文件上传类型是否正确
         $this->checkType();
//        判断文件上传大小是否符合要求
         $this->checkSize();
//        给文件取名(使用原名还是新名称)

//        规定新文文件的保存地址(保存在一个文件夹中还是按年月日保存)

    }

//    检查文件上传的类型是否符合要求
    public function checkType(){
        if(!in_array($this->fileType,$this->allowType)){
            $this->error = "Typeerror";
            echo "文件 <strong> {$this->originName} </strong> 类型不符合，请上传类型为".implode(',',$this->allowType)."的文件";
        }
    }

//    检查文件大小是否符合要求
    public function checkSize(){
        if($this->fileSize > $this->maxSize){
            $this->error = "Typeerror";
            echo "文件 <strong> {$this->originName} </strong> 过大，请把文件大小控制在".($this->maxSize/1024/1024)."Mb";
        }
    }


//    得到上传文件的信息
    public function getFileMsg(){
        echo "<pre>";
        echo "上传文件信息：<br>";
        echo "<br>文件名称：".$this->originName;
        echo "<br>文件类型：".$this->fileType;
        echo "<br>文件大小：".$this->fileSize;
        echo "<br>临时文件路径：".$this->tmpName;
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
