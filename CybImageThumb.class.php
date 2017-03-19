<?php
/**
 * Created by PhpStorm.
 * User: cyb
 * Date: 2017/3/18
 * Time: 10:36
 *
 * 描述：简单的缩略图类（GD库）
 *
 *
 *
 * 功能：
 * 1.生成缩略图
 * 2.向原图或者缩略图中加入水印
 *
 */

class CybThumb{

//    属性
    private $originDir;         //原图片路径
    private $originName;        //原图片名称
    private $originWidth;       //原图宽度
    private $originHeight;      //原图宽度
    private $originType;        //原图类型

    private $thumbDir;          //缩略图路径
    private $thumbWidth;        //缩略图宽度
    private $thumbHeight;       //缩略图高度


//    构造函数
    public function __construct($orginfile){
        $this->originDir = $orginfile;
//        得到文件名
        $this->originName = $this->getImageName();
//        获得原图宽度、高度、类型
        list($this->originWidth,$this->originHeight,$type)= getimagesize($this->originDir);
        switch($type){
            case 1:
                $this->originType = "gif";
                break;
            case 2:
                $this->originType = "jpeg";
                break;
            case 3:
                $this->originType = "png";
                break;
            case 4:
                $this->originType = "swf";
                break;
            case 5:
                $this->originType = "psd";
                break;
            case 6:
                $this->originType = "bmp";
                break;
            default:
                die("上传的图片错误,请正确上传要处理的图片");
                break;
        }
//        默认的缩略图路径
        $this->getThumbDir();
    }
//    得到缩略图保存路径
    private function getThumbDir($rootDir=null,$type = false){
        $thumbDir = __DIR__.DIRECTORY_SEPARATOR."imgages_thumb".DIRECTORY_SEPARATOR;
        $this->thumbDir = $thumbDir.'thumb_'.$this->originName;
    }

//    重新设置缩略图保存目录
    public function setThumbDir($newDir,$type=false){
//        规范 / 或 \
        $newDir = str_replace("/",DIRECTORY_SEPARATOR,$newDir);
        $newDir = str_replace("\\",DIRECTORY_SEPARATOR,$newDir);
//        echo $newDir;
//        die;
//        判断是否需要以年月日文件夹树保存缩略图
        if(!$type){
//        判断缩略图路径是否存在，如果不存在则新建目录
            if(!$this->dirIsExist($newDir)){
                $this->mkNewDir($newDir);
            }
        }else{
            //            需要使用年月日
            $newDir = $this->getDateDir($newDir);
        }

        $this->thumbDir = $newDir.'thumb_'.$this->originName;
    }

//    得到文件名(包含后缀名)
    private function getImageName(){
        $origindir = str_replace("/",DIRECTORY_SEPARATOR,$this->originDir);
        $origindir = str_replace("\\",DIRECTORY_SEPARATOR,$origindir);
        return substr($this->originDir,(strrpos($origindir,DIRECTORY_SEPARATOR))+1);
    }

//   制作缩略图并保存----按百分比压缩
    public function createThumbPercent($percent){

        $this->thumbWidth = $this->originWidth * $percent;
        $this->thumbHeight = $this->originHeight * $percent;

        $newImage = $this->createimage();
//        根据图片类型把原图片上传到服务器缓存--创建一个缓存图片
        $funcName = "imagecreatefrom".$this->originType;
        $originImage = $funcName($this->originDir);

//      生成缩略图
        imagecopyresampled($newImage,$originImage,0,0,0,0,$this->thumbWidth,$this->thumbHeight,$this->originWidth,$this->originHeight);

//        保存图片
        $funcName1 = "image".$this->originType;
        $funcName1($newImage,$this->thumbDir);
    }

//    制作缩略图并保存 -----按给定的宽高压缩
    public function createThumbSize($width,$height){

    }




//    根据缩略图宽高创建一个真彩色图像
    private function createimage(){
       return imagecreatetruecolor($this->thumbWidth,$this->thumbHeight);
    }


//    获得以年月日命名的文件夹树
    private function getDateDir($thumbDir){
//        拼接年文件夹
        $thumbDir = $thumbDir.date("Y").DIRECTORY_SEPARATOR;
        if(!($this->dirIsExist($thumbDir))){
            $this->mkNewDir($thumbDir);  //根/年/
        }
//        拼接月文件夹
        $thumbDir .=date("m").DIRECTORY_SEPARATOR;
        if(!($this->dirIsExist($thumbDir))){
            $this->mkNewDir($thumbDir);  //根/年/月/
        }
//        拼接日文件夹
        $thumbDir .=date("d").DIRECTORY_SEPARATOR;
        if(!($this->dirIsExist($thumbDir))){
            $this->mkNewDir($thumbDir);  //根/年/月/日/
        }
        return $thumbDir;
    }

//    判断目录是否存在
    private function dirIsExist($dir){
        return file_exists($dir);
    }

//    创建新目录
    private function mkNewDir($dir){
        return mkdir($dir);
    }


//   打印原图片信息
    public function showOriginImageMsg(){
        echo "<pre>";
            echo "<h3>要进行压缩处理的原图片信息：</h3>";
            echo "<br>原图片路径：".$this->originDir;
            echo "<br>原图片名称：".$this->originName;
            echo "<br>原图片宽度：".$this->originWidth."px";
            echo "<br>原图片高度：".$this->originHeight."px";
            echo "<br>原图片类型：".$this->originType;
            echo "<br>缩略图保存路径：".$this->thumbDir;
//            echo __DIR__;
        echo "</pre>";
    }


}