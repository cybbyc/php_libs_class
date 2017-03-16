<?php
/**
+-----------------------------------------------------------------------------
 * 文件上传类
+-----------------------------------------------------------------------------
 * @author Administrator
+-----------------------------------------------------------------------------
 */
class FileUpload{

    private $filepath;  //保存路径
    private $allowtype=array('gif','jpg','jpeg','png','txt');
    private $maxsize=1000000;  //最大允许上传大小
    private $israndname=true; //是否随机
    private $orginame; //原始文件名
    private $tmpname;  //临时文件名
    private $newname;  //新文件名
    private $filetype; //文件类型
    private $filesize; //文件大小
    private $errornum=''; //错误号
    private $errormsg; //错误信息
    /**
    +------------------------------------------------------------------------------
     *构造函数
    +------------------------------------------------------------------------------
     * @param string $savepath  保存路径
     * @param string $allowtype 允许类型
     * @param string $maxsize  允许大小
     *
    +------------------------------------------------------------------------------
     */
    function __construct($option=array()){
        foreach ($option as $key=>$value){

            if (!in_array($key,get_class_vars(get_class($this)))){
                continue;
            }
            $this->setOption($key, $value);
        }
    }
    function uploadfile($field) {
        $return=true;
        if (!$this->CheckPath()) {
            $this->errormsg=$this->geterrorNum();
            return false;
        }
        $name=$_FILES[$field]['name'];
        $tmpname=$_FILES[$field]['tmp_name'];
        $filesize=$_FILES[$field]['size'];
        $error=$_FILES[$field]['error'];
        if (is_array($name)) {
            $errors=array();
            for ($i=0;$i<count($name);$i++){
                if ($this->getFile($name[$i],$tmpname[$i],$filesize[$i],$errors[$i])) {
                    if (!$this->CheckSize() && !$this->CheckType()) {
                        $errors=$this->getErrorNum();
                        return false;
                    }
                }else{
                    $errors=$this->getErrorNum();
                    return false;
                }
                if (!$return) {
                    $this->getFile();
                }
            }
            if ($return) {
                $fileNames=array();
                for ($i=0;$i<count($name);$i++){
                    if ($this->getFile($name[$i], $tmpname[$i], $filesize[$i], $filesize[$i])) {
                        $this->SetFileName();
                        if (!$this->MoveFile()) {
                            $errors[]=$this->getErrorNum();
                            $return=false;
                        }else{
                            $fileNames[]=$this->getNewName();
                        }
                    }
                }
                $this->newname=$fileNames;
            }
            $this->errormsg=$errors;
            return $return;

        }else{
            if($this->getFile($name,$tmpname,filesize,$error)){
                if(!$this->CheckSize()){
                    return false;
                }
                if(!$this->CheckType()){
                    return false;
                }
                $this->SetFileName();
                if ($this->MoveFile()) {
                    return true;
                }
            }else{
                return false;
            }

            if (!$return) {
                $this->setOption('ErrorNum', 0);
                $this->errormsg=$this->geterrorNum();
            }
            return $return;
        }
    }
    /**
    +------------------------------------------------------------------------
     *设置类属性值函数
    +------------------------------------------------------------------------
     * @param mix $key
     * @param mix $value
     */
    private function setOption($key,$value){
        $key=strtolower($key);
        $this->$key=$value;
    }
    /**
    +---------------------------------------------------------------------------
     * 获取文件变量参数函数
    +---------------------------------------------------------------------------
     * @param string $name
     * @param string $tmp_name
     * @param number $size
     * @param number $error
     */
    private function getFile($name,$tmpname,$filetype,$filesize,$error=0){

        $this->setOption('TmpName', $tmpname);
        $this->setOption('OrgiName', $name);
        $arrstr=explode('.', $name);
        $this->setOption('FileType', $arrstr[count($arrstr)-1]);
        $this->setOption('FileSize', $filesize);
        return true;
    }
    /**
    +-------------------------------------------------------------------------
     * 检查上传路径函数
    +-------------------------------------------------------------------------
     * @return boolean
     */
    private function CheckPath(){
        if(empty($this->filepath)){
            $this->setOption('ErrorNum', -5);
            return false;
        }
        if (!file_exists($this->filepath)||!is_writable($this->filepath)) {
            if (!@mkdir($this->filepath,0755)) {
                $this->setOption('ErrorNum',-4);
                return false;
            }
        }
        return true;
    }
    private function Is_Http_Post(){
        if (!is_uploaded_file($this->tmpname)) {
            $this->setOption('ErrorNum',-6);
            return false;
        }else{
            return true;
        }
    }
    /**
    +--------------------------------------------------------------------
     *检查文件尺寸函数
    +--------------------------------------------------------------------
     * @return boolean
     */
    private function CheckSize(){
        if ($this->filesize>$this->maxsize) {
            $this->setOption('ErrorNum', -2);
            return false;
        }else{
            return true;
        }
    }
    /**
    +---------------------------------------------------------------
     * 检查文件类型函数
    +---------------------------------------------------------------
     * @return boolean
     */
    private function CheckType(){
        if (in_array($this->filetype, $this->allowtype)) {
            return true;
        }else{
            $this->setOption('ErrorNum', -1);
            return false;
        }
    }
    private function SetFileName(){
        if ($this->israndname) {
            $this->setOption('NewName', $this->RandName());
        }else{
            $this->setOption('NewName',$this->orginame);
        }
    }
    /**
    +-----------------------------------------------------------------
     * 获取新文件名
    +------------------------------------------------------------------
     */
    public function getNewName() {
        return $this->newname;
    }
    private function RandName(){
        $rule=date("YmdHis").rand(0, 999);
        return $rule.'.'.$this->filetype;
    }
    private function MoveFile(){
        if ($this->errornum) {
            $filepath=rtrim($this->filaepath,'/').'/';
            $filepath.=$this->newname;
            if (@move_uploaded_file($this->tmpname,$filepath)) {
                return true;
            }else{
                $this->errormsg=$this->setOption('ErrorNum',-3 );
            }
        }else{
            return false;
        }
    }
    /**
    +----------------------------------------------------------------
     * 错误信息函数
    +----------------------------------------------------------------
     * @return string
     */
    function getErrorNum() {
        $erstr="上传文件<font color='red'>{$this->orginame}</font>出错";
        switch ($this->errornum) {
            case 4:
                $erstr.="没有文件被上传";
                break;
            case 3:
                $erstr.="文件只被部分上传";
                break;
            case 2:
                $erstr.="上传文件超过了HTML表单MAX_FILE_SIZE指定的值";
                break;
            case 1:
                $erstr.="上传文件超过了php.ini配置文件中upload_max_filesize的值";
                break;
            case 0:
                $erstr="上传{$this->orginame}成功";
                break;
            case -1:
                $erstr="未允许的类型";
                break;
            case -2:
                $erstr.="文件过大，不能超过{$this->maxsize}个字节";
                break;
            case -3:
                $erstr.="上传失败";
                break;
            case -4:
                $erstr="创建上传目录失败，请重新指定上传目录";
                break;
            case -5:
                $erstr="未指定上传路径";
                break;
            case -6:
                $erstr="非法操作";
                break;
            default:
                $erstr.="未知错误";

        }
        return $erstr;
    }
}