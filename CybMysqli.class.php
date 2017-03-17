<?php
/**
 * Created by PhpStorm.
 * User: cyb
 * Date: 2017/3/15
 * Time: 14:20
 * version:1.0 (已完成)
 * 描述：mysqli过程化风格
 *  1.通过静态方法实例化类
 *  2.实现了单条语句和多条语句查询
 *  3.插入数据、修改数据
 *  4.删除一条河多条数据
 *  5.打印数据
 *  6.查看mysql服务器端和客户端的信息
 */

header('content-type:text/html;charset=utf-8');

class CybMysqli{

	// 属性(私有)
	private static $dbconn =false;
    private $host;
    private $port;
    private $user;
    private $pwd;
    private $dbname;
    private $charset;
    private $link;

//    私有的构造方法
    private function __construct($config=array()){
        $this->host = isset($config['host'])  ? $config['host'] : 'localhost';
        $this->port = isset($config['port'])  ? $config['port'] : '3306';
        $this->user = isset($config['user'])  ? $config['user'] : 'root';
        $this->pwd = isset($config['pwd']) ? $config['pwd'] : '';
        $this->dbname = isset($config['dbname']) ? $config['dbname'] : 'test';
        $this->charset=isset($config['charset']) ? $config['charset'] : 'utf8';
//        链接数据库
        $this->connectDb();
//        选择数据库
        $this->selectDb();
//        设置字符编码
        $this->charsetDb();
    }

//  析构函数
    public function __destruct(){
        return $this->closeDb();
    }

//    链接数据库
    private function connectDb(){
        $this->link = mysqli_connect($this->host.':'.$this->port,$this->user,$this->pwd);
//        如果链接出错，打印错误信息
        if(!$this->link){
            echo "数据库连接失败<br>";
            echo "错误编码：".mysqli_errno($this->link)."<br>";
            echo "错误信息：".mysqli_error($this->link)."<br>";
            die;
        }
    }

//    选择数据库
    private function selectDb(){
            mysqli_select_db($this->link,$this->dbname);
    }

//    设置字符编码
    private function charsetDb(){
        mysqli_query($this->link,"set names {$this->charset}");
    }

//    公用的静态方法，用于实例化该类的对象
    public static function getInstance(){
        if(self::$dbconn == false){
            self::$dbconn = new self;
        }
        return self::$dbconn;
    }

    /*
     * 执行sql语句
     * @param $sql    sql语句
     * @return      返回一个成功执行的sql标记
     *
     * */
    public function query($sql)
    {
        $result = mysqli_query($this->link, $sql);
//        判断sql语句是否执行成功
        if (!$result) {
            echo "sql语句执行失败<br>";
            echo "错误编码：" . mysqli_errno($this->link) . "<br>";
            echo "错误信息：" . mysqli_error($this->link) . "<br>";
            echo "请查看sql语句错误并改正：" . $sql;
            die;
        }
        return $result;
    }

    /*
      * 查询一条记录
      * @param $sql    查询sql语句
      * @param $type     查询的方式
      * @return      返回查询得到的一条数据数组
      *
      * */
    public function getOne($sql,$type="assoc"){
        $result = $this->query($sql);
        if(!in_array($type,array("assoc","array","row","all"))){
//            如果参数设置不正确，则强制性使用assoc
            $type = "assoc";
        }
        $funcName = "mysqli_fetch_".$type;
//      返回一条数据
        return $funcName($result);
    }

    /*
      * 查询多条数据
      * @param $sql    查询sql语句
      * @param $type     查询的方式
      * @return      返回查询得到的二维数组
      *
      * */
    public function getAll($sql,$type="assoc"){
        $result = $this->query($sql);
        $data = array();
        if(!in_array($type,array("assoc","array","row","all"))){
//            如果参数设置不正确，则强制性使用assoc
            $type = "assoc";
        }
        $funcName = "mysqli_fetch_".$type;
        while($row = $funcName($result) ){
            $data[] = $row;
        }
        return $data;
    }

    /*
      * 添加数据
      * @param $table    要操作的数据库表
      * @param $data     要修改的行数据，数组形式
      * @return      返回当前插入数据的id号
      *
      * */
    public function insert($table,$data)
    {
        $k_str = '';
        $v_str = '';
        foreach ($data as $k => $v) {
//            如果$v中有单引号或者双引号转化为特殊字符：&apos;和 &quot;
            $v = str_replace('"', '&quot;', $v);
            $v = str_replace("'", '&apos;', $v);
//            拼接键字符串和值字符串
            $k_str .= $k . ',';
            $v_str .= '"' . $v . '",';
        }
//        去掉两边多余的逗号
        $k_str = trim($k_str, ',');
        $v_str = trim($v_str, ',');
//        组成完整的sql语句
        $sql = 'insert into '.$table .'('. $k_str . ') VALUES(' . $v_str.')';
//        echo $sql;
//        执行sql语句
        $this->query($sql);
//        返回新增操作的id值
        return $this->lastInsertId();
    }

//获取返回最后一次插入的id号
    private function lastInsertId(){
        return mysqli_insert_id($this->link);
    }

    /*
     * 修改数据
     * @param $table    要操作的数据库表
     * @param $data     要修改的行数据，数组形式
     * @param $where    修改的条件
     * @return      返回当前删除操作影响的行数
     *
     * */
    public function update($table,$data,$where){
        $str = '';
        foreach($data as $k=>$v){
//            如果$v中有单引号或者双引号转化为特殊字符：&apos;和 &quot;
            $v = str_replace('"', '&quot;', $v);
            $v = str_replace("'", '&apos;', $v);

            $str.=$k.'="'.$v.'",';
        }
        $str=trim($str,',');
//        拼接sql语句
        $sql = 'update '.$table.' set '.$str.' where '.$where;
//        echo $sql;
//        执行sql语句
        $this->query($sql);
//        返回当前修改操作影响的行数
        return mysqli_affected_rows($this->link);
    }

    /*
     * 删除一行数据
     * @param $table    要操作的数据库表
     * @param $where    删除的条件，可以是字符串或者数组类型
     * @return      返回当前删除操作影响的行数
     *
     * */
    public function deleteOne($table,$where){
//        判断是否是数组
        if(is_array($where)){
            foreach($where as $k=>$v){
                $condition = $k.'='.$v;
            }
        }else{
            $condition = $where;
        }
        $sql = 'delete from '.$table.' where '.$condition;
        $this->query($sql);
//        返回当前删除操作影响的行数
        return mysqli_affected_rows($this->link);
    }

    /*
     * 删除多行数据
     * @param $table    要操作的数据库表
     * @param $where    删除的条件，可以是字符串或者数组类型
     * @return      返回当前删除操作影响的行数
     *
     * */
    public function deleteAll($table,$where){
//        判断是否是数组
        if(is_array($where)){
            foreach($where as $k=>$v){
//                如果是多维数组,及多个需要删除的条件
                if(is_array($v)){
                    $condition = $k .' in ('.implode(',',$v).')';
                }else{
                    $condition = $k.'='.$v;
                }
            }
        }else{
            $condition = $where;
        }
        $sql = 'delete from '.$table.' where '.$condition;
        $this->query($sql);
//        返回当前删除操作影响的行数
        return mysqli_affected_rows($this->link);
    }

/*
 *
 *
 * 以下是数据库操作类的额外功能方法
 *
 *
 * */
//    使用prin_r打印数据
    public function pr($arr){
        echo "<pre>";
        print_r($arr);
        echo "</pre>";
    }

//    使用var_dump打印数据
    public function vd($arr){
        echo "<pre>";
        var_dump($arr);
        echo "</pre>";
    }

//    返回数据库连接的默认字符集
    public function return_encoding(){
        echo mysqli_character_set_name($this->link);
    }

//    关闭数据库连接，
    public function closeDb(){
//        判断数据库是否处于连接状态
        if(is_resource($this->link)){
            return mysqli_close($this->link);
        }else{
            return true;
        }
    }


/*
 * 查看数据库相关信息的函数
 *
 * */

//    查看当前数据库系统中的所有数据库
    public function showDbs(){
        $sql = "show databases";
        $list = $this->getAll($sql);
        return $list;
    }

//    查看当前数据库的表
    public function showTabs(){
        $sql = "show tables";
        $list = $this->getAll($sql);
        return $list;
    }

//    查看MySQL服务器信息(主机名、连接类型、协议版本、服务器版本)
    public function showMysqlServerMessage(){
        $message = array();
//        主机名、连接类型
        $message["host"] = mysqli_get_host_info($this->link);
//        协议版本
        $message["proro"] = mysqli_get_proto_info($this->link);
//        MySQL服务器版本
        $message["version"] = mysqli_get_server_info($this->link);
        return $message;
    }

//    查看客户端信息
    public function showClientMessage(){
        $message = array();
//        客户端mysql版本号，整型
        $message["version_i"] = mysqli_get_client_version($this->link);
        $message["version_s"] = mysqli_get_client_info($this->link);
//        返回每个客户端进程的统计信息
//        $message["stats"] = mysqli_get_client_stats();
        return $message;
    }

}
