<?php
header('content-type:text/html;charset=utf-8');
/*
�������㵥��ģʽ�ı�Ҫ����
(1)˽�еĹ��췽��-Ϊ�˷�ֹ������ʹ��new�ؼ���ʵ��������
(2)˽�еĳ�Ա����-Ϊ�˷�ֹ���������������Ŷ��������
(3)˽�еĿ�¡����-Ϊ�˷�ֹ������ͨ��clone������һ������
(4)���еľ�̬����-Ϊ�����û�����ʵ��������Ĳ���
*/
class ConnectMysqli{
  //˽�е�����
  private static $dbcon=false;
  private $host;
  private $port;
  private $user;
  private $pass;
  private $db;
  private $charset;
  private $link;	//���ݿ����ӱ�־
  //˽�еĹ��췽��
  private function __construct($config=array()){
    $this->host = $config['host'] ? $config['host'] : 'localhost';
    $this->port = $config['port'] ? $config['port'] : '3306';
    $this->user = $config['user'] ? $config['user'] : 'root';
    $this->pass = $config['pass'] ? $config['pass'] : '';
    $this->db = $config['db'] ? $config['db'] : 'test';
    $this->charset=isset($arr['charset']) ? $arr['charset'] : 'utf8';
    //�������ݿ�
    $this->db_connect();
    //ѡ�����ݿ�
    $this->db_usedb();
    //�����ַ���
    $this->db_charset();
   }
   //�������ݿ�
   private function db_connect(){
    $this->link=mysqli_connect($this->host.':'.$this->port,$this->user,$this->pass);
	// ������ʧ�ܣ�������ʾ
    if(!$this->link){
      echo "���ݿ�����ʧ��<br>";
      echo "�������".mysqli_errno($this->link)."<br>";
      echo "������Ϣ".mysqli_error($this->link)."<br>";
      exit;
    }
   }
   
   //�����ַ���
    private function db_charset(){
     mysqli_query($this->link,"set names {$this->charset}");
    }
	
   //ѡ�����ݿ�
   private function db_usedb(){
	// mysqli_query($this->link,"use {$this->db}");
	 mysqli_select_db($this->link,$this->db);
   }
   
   
   //˽�еĿ�¡
   private function __clone(){
     die('clone is not allowed');
   }
   
   //���õľ�̬����
   public static function getIntance(){
     if(self::$dbcon==false){
      self::$dbcon=new self;
     }
     return self::$dbcon;
   }
   
   
   
   

   
   //ִ��sql���ķ���
    public function query($sql){
     $res=mysqli_query($this->link,$sql);
     if(!$res){
      echo "sql���ִ��ʧ��<br>";
      echo "���������".mysqli_errno($this->link)."<br>";
      echo "������Ϣ��".mysqli_error($this->link)."<br>";
     }
     return $res;
   }
   
   //��ӡ����
    public function p($arr){
      echo "<pre>";
      print_r($arr);
      echo "</pre>";
    }
	
    public function v($arr){
    echo "<pre>";
      var_dump($arr);
      echo "</pre>";
    }
	
    //������һ����¼id
    public function getInsertid(){
     return mysqli_insert_id($this->link);
    }
	
   /**
    * ��ѯĳ���ֶ�
    * @param
    * @return string or int
    */
    public function getOne($sql){
     $query=$this->query($sql);
      return mysqli_free_result($query);
    }
    //��ȡһ�м�¼,return array һά����
    public function getRow($sql,$type="assoc"){
     $query=$this->query($sql);
     if(!in_array($type,array("assoc",'array',"row"))){
       die("mysqli_query error");
     }
     $funcname="mysqli_fetch_".$type;
     return $funcname($query);
    }
	
    //��ȡһ����¼,ǰ������ͨ����Դ��ȡһ����¼
    public function getFormSource($query,$type="assoc"){
    if(!in_array($type,array("assoc","array","row")))
    {
      die("mysqli_query error");
    }
    $funcname="mysqli_fetch_".$type;
    return $funcname($query);
    }
	
    //��ȡ�������ݣ���ά����
    public function getAll($sql){
     $query=$this->query($sql);
     $list=array();
     while ($r=$this->getFormSource($query)) {
      $list[]=$r;
     }
     return $list;
    }
     /**
     * ����������ݵķ���
     * @param string $table ����
     * @param string orarray $data [����]
     * @return int ������ӵ�id
     */
     public function insert($table,$data){
     //�������飬�õ�ÿһ���ֶκ��ֶε�ֵ
     $key_str='';
     $v_str='';
     foreach($data as $key=>$v){
      if(empty($v)){
       die("error");
     }
        //$key��ֵ��ÿһ���ֶ�sһ���ֶ�����Ӧ��ֵ
        $key_str.=$key.',';
        $v_str.="'$v',";
     }
     $key_str=trim($key_str,',');
     $v_str=trim($v_str,',');
     //�ж������Ƿ�Ϊ��
     $sql="insert into $table ($key_str) values ($v_str)";
     $this->query($sql);
    //������һ�����Ӳ�������IDֵ
     return $this->getInsertid();
   }
   
   /*
    * ɾ��һ�����ݷ���
    * @param1 $table, $where=array('id'=>'1') ���� ����
    * @return ��Ӱ�������
    */
    public function deleteOne($table, $where){
      if(is_array($where)){
        foreach ($where as $key => $val) {
          $condition = $key.'='.$val;
        }
      } else {
        $condition = $where;
      }
      $sql = "delete from $table where $condition";
      $this->query($sql);
      //������Ӱ�������
      return mysqli_affected_rows($this->link);
    }
    /*
    * ɾ���������ݷ���
    * @param1 $table, $where ���� ����
    * @return ��Ӱ�������
    */
    public function deleteAll($table, $where){
      if(is_array($where)){
        foreach ($where as $key => $val) {
          if(is_array($val)){
            $condition = $key.' in ('.implode(',', $val) .')';
          } else {
            $condition = $key. '=' .$val;
          }
        }
      } else {
        $condition = $where;
      }
      $sql = "delete from $table where $condition";
      $this->query($sql);
      //������Ӱ�������
      return mysqli_affected_rows($this->link);
    }
   /**
    * [�޸Ĳ���description]
    * @param [type] $table [����]
    * @param [type] $data [����]
    * @param [type] $where [����]
    * @return [type]
    */
   public function update($table,$data,$where){
     //�������飬�õ�ÿһ���ֶκ��ֶε�ֵ
     $str='';
    foreach($data as $key=>$v){
     $str.="$key='$v',";
    }
    $str=rtrim($str,',');
    //�޸�SQL���
    $sql="update $table set $str where $where";
    $this->query($sql);
    //������Ӱ�������
    return mysqli_affected_rows($this->link);
   }
}