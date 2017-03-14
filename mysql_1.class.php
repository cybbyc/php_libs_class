<?php
#==================================================================================================
# Filename: /db/db_mysqli.php
# Note : �������ݿ��࣬MySQLi��
#==================================================================================================
#[���sql]
class db_mysqli
{
 var $query_count = 0;
 var $host;
 var $user;
 var $pass;
 var $data;
 var $conn;
 var $result;
 var $prefix = "qinggan_";
 //���ؽ�������ͣ�Ĭ��������+�ַ�
 var $rs_type = MYSQLI_ASSOC;
 var $query_times = 0;#[��ѯʱ��]
 var $conn_times = 0;#[�������ݿ�ʱ��]
 var $unbuffered = false;
 //�����ѯ�б�
 var $querylist;
 var $debug = false;
 #[���캯��]
 function __construct($config=array())
 {
 $this->host = $config['host'] ? $config['host'] : 'localhost';
 $this->port = $config['port'] ? $config['port'] : '3306';
 $this->user = $config['user'] ? $config['user'] : 'root';
 $this->pass = $config['pass'] ? $config['pass'] : '';
 $this->data = $config['data'] ? $config['data'] : '';
 $this->debug = $config["debug"] ? $config["debug"] : false;
 $this->prefix = $config['prefix'] ? $config['prefix'] : 'qinggan_';
 if($this->data)
 {
  $ifconnect = $this->connect($this->data);
  if(!$ifconnect)
  {
  $this->conn = false;
  return false;
  }
 }
 return true;
 }
 #[����PHP4]
 function db_mysqli($config=array())
 {
 return $this->__construct($config);
 }
 #[�������ݿ�]
 function connect($database="")
 {
 $start_time = $this->time_used();
 if(!$this->port) $this->port = "3306";
 $this->conn = @mysqli_connect($this->host,$this->user,$this->pass,"",$this->port) or false;
 if(!$this->conn)
 {
  return false;
 }
 $version = $this->get_version();
 if($version>"4.1")
 {
  mysqli_query($this->conn,"SET NAMES 'utf8'");
  if($version>"5.0.1")
  {
  mysqli_query($this->conn,"SET sql_mode=''");
  }
 }
 $end_time = $this->time_used();
 $this->conn_times += round($end_time - $start_time,5);#[�������ݿ��ʱ��]
 $ifok = $this->select_db($database);
 return $ifok ? true : false;
 }
 function select_db($data="")
 {
 $database = $data ? $data : $this->data;
 if(!$database)
 {
  return false;
 }
 $this->data = $database;
 $start_time = $this->time_used();
 $ifok = mysqli_select_db($this->conn,$database);
 if(!$ifok)
 {
  return false;
 }
 $end_time = $this->time_used();
 $this->conn_times += round($end_time - $start_time,5);#[�������ݿ��ʱ��]
 return true;
 }
 #[�ر����ݿ����ӣ�����ʹ�ó�������ʱ�ù���ʧЧ]
 function close()
 {
 if(is_resource($this->conn))
 {
  return mysqli_close($this->conn);
 }
 else
 {
  return true;
 }
 }
 function __destruct()
 {
 return $this->close();
 }
 function set($name,$value)
 {
 if($name == "rs_type")
 {
  $value = strtolower($value) == "num" ? MYSQLI_NUM : MYSQLI_ASSOC;
 }
 $this->$name = $value;
 }
 function query($sql)
 {
 if(!is_resource($this->conn))
 {
  $this->connect();
 }
 else
 {
  if(!mysql_ping($this->conn))
  {
   $this->close();
   $this->connect();
  }
 }
 if($this->debug)
 {
  $sqlkey = md5($sql);
  if($this->querylist)
  {
  $qlist = array_keys($this->querylist);
  if(in_array($sqlkey,$qlist))
  {
   $count = $this->querylist[$sqlkey]["count"] + 1;
   $this->querylist[$sqlkey] = array("sql"=>$sql,"count"=>$count);
  }else{
   $this->querylist[$sqlkey] = array("sql"=>$sql,"count"=>1);
  }
  }
  else{
  $this->querylist[$sqlkey] = array("sql"=>$sql,"count"=>1);
  }
 }
 $start_time = $this->time_used();
 $func = $this->unbuffered && function_exists("mysqli_multi_query") ? "mysqli_multi_query" : "mysqli_query";
 $this->result = @$func($this->conn,$sql);
 $this->query_count++;
 $end_time = $this->time_used();
 $this->query_times += round($end_time - $start_time,5);#[��ѯʱ��]
 if(!$this->result)
 {
  return false;
 }
 return $this->result;
 }
 function get_all($sql="",$primary="")
 {
 $result = $sql ? $this->query($sql) : $this->result;
 if(!$result)
 {
  return false;
 }
 $start_time = $this->time_used();
 $rs = array();
 $is_rs = false;
 while($rows = mysqli_fetch_array($result,$this->rs_type))
 {
  if($primary && $rows[$primary])
  {
  $rs[$rows[$primary]] = $rows;
  }
  else
  {
  $rs[] = $rows;
  }
  $is_rs = true;
 }
 $end_time = $this->time_used();
 $this->query_times += round($end_time - $start_time,5);#[��ѯʱ��]
 return ($is_rs ? $rs : false);
 }
 function get_one($sql="")
 {
 $start_time = $this->time_used();
 $result = $sql ? $this->query($sql) : $this->result;
 if(!$result)
 {
  return false;
 }
 $rows = mysqli_fetch_array($result,$this->rs_type);
 $end_time = $this->time_used();
 $this->query_times += round($end_time - $start_time,5);#[��ѯʱ��]
 return $rows;
 }
 function insert_id($sql="")
 {
 if($sql)
 {
  $rs = $this->get_one($sql);
  return $rs;
 }
 else
 {
  return mysqli_insert_id($this->conn);
 }
 }
 function insert($sql)
 {
 $this->result = $this->query($sql);
 $id = $this->insert_id();
 return $id;
 }
 function all_array($table,$condition="",$orderby="")
 {
 if(!$table)
 {
  return false;
 }
 $table = $this->prefix.$table;
 $sql = "SELECT * FROM ".$table;
 if($condition && is_array($condition) && count($condition)>0)
 {
  $sql_fields = array();
  foreach($condition AS $key=>$value)
  {
  $sql_fields[] = "`".$key."`='".$value."' ";
  }
  $sql .= " WHERE ".implode(" AND ",$sql_fields);
 }
 if($orderby)
 {
  $sql .= " ORDER BY ".$orderby;
 }
 $rslist = $this->get_all($sql);
 return $rslist;
 }
 function one_array($table,$condition="")
 {
 if(!$table)
 {
  return false;
 }
 $table = $this->prefix.$table;
 $sql = "SELECT * FROM ".$table;
 if($condition && is_array($condition) && count($condition)>0)
 {
  $sql_fields = array();
  foreach($condition AS $key=>$value)
  {
  $sql_fields[] = "`".$key."`='".$value."' ";
  }
  $sql .= " WHERE ".implode(" AND ",$sql_fields);
 }
 $rslist = $this->get_one($sql);
 return $rslist;
 }
 //������д��������
 function insert_array($data,$table,$insert_type="insert")
 {
 if(!$table || !is_array($data) || !$data)
 {
  return false;
 }
 $table = $this->prefix.$table;//�Զ����ӱ�ǰ׺
 if($insert_type == "insert")
 {
  $sql = "INSERT INTO ".$table;
 }
 else
 {
  $sql = "REPLACE INTO ".$table;
 }
 $sql_fields = array();
 $sql_val = array();
 foreach($data AS $key=>$value)
 {
  $sql_fields[] = "`".$key."`";
  $sql_val[] = "'".$value."'";
 }
 $sql.= "(".(implode(",",$sql_fields)).") VALUES(".(implode(",",$sql_val)).")";
 return $this->insert($sql);
 }
 //��������
 function update_array($data,$table,$condition)
 {
 if(!$data || !$table || !$condition || !is_array($data) || !is_array($condition))
 {
  return false;
 }
 $table = $this->prefix.$table;//�Զ����ӱ�ǰ׺
 $sql = "UPDATE ".$table." SET ";
 $sql_fields = array();
 foreach($data AS $key=>$value)
 {
  $sql_fields[] = "`".$key."`='".$value."'";
 }
 $sql.= implode(",",$sql_fields);
 $sql_fields = array();
 foreach($condition AS $key=>$value)
 {
  $sql_fields[] = "`".$key."`='".$value."' ";
 }
 $sql .= " WHERE ".implode(" AND ",$sql_fields);
 return $this->query($sql);
 }
 function count($sql="")
 {
 if($sql)
 {
  $this->rs_type = MYSQLI_NUM;
  $this->query($sql);
  $rs = $this->get_one();
  $this->rs_type = MYSQLI_ASSOC;
  return $rs[0];
 }
 else
 {
  return mysqli_num_rows($this->result);
 }
 }
 function num_fields($sql="")
 {
 if($sql)
 {
  $this->query($sql);
 }
 return mysqli_num_fields($this->result);
 }
 function list_fields($table)
 {
 $rs = $this->get_all("SHOW COLUMNS FROM ".$table);
 if(!$rs)
 {
  return false;
 }
 foreach($rs AS $key=>$value)
 {
  $rslist[] = $value["Field"];
 }
 return $rslist;
 }
 #[��ʾ����]
 function list_tables()
 {
 $rs = $this->get_all("SHOW TABLES");
 return $rs;
 }
 function table_name($table_list,$i)
 {
 return $table_list[$i];
 }
 function escape_string($char)
 {
 if(!$char)
 {
  return false;
 }
 return mysqli_escape_string($this->conn,$char);
 }
 function get_version()
 {
 return mysqli_get_server_info($this->conn);
 }
 function time_used()
 {
 $time = explode(" ",microtime());
 $used_time = $time[0] + $time[1];
 return $used_time;
 }
 //Mysql�Ĳ�ѯʱ��
 function conn_times()
 {
 return $this->conn_times + $this->query_times;
 }
 //MySQL��ѯ����
 function conn_count()
 {
 return $this->query_count;
 }
 # ��ЧSQL���ɲ�ѯ�����ʺϵ����ѯ
 function phpok_one($tbl,$condition="",$fields="*")
 {
 $sql = "SELECT ".$fields." FROM ".$this->db->prefix.$tbl;
 if($condition)
 {
  $sql .= " WHERE ".$condition;
 }
 return $this->get_one($sql);
 }
 function debug()
 {
 if(!$this->querylist || !is_array($this->querylist) || count($this->querylist) < 1)
 {
  return false;
 }
 $html = '<table cellpadding="0" cellspacing="0" width="100%" bgcolor="#CECECE"><tr><td>';
 $html.= '<table cellpadding="1" cellspacing="1" width="100%">';
 $html.= '<tr><th bgcolor="#EFEFEF" height="30px">SQL</th><th bgcolor="#EFEFEF" width="80px">��ѯ</th></tr>';
 foreach($this->querylist AS $key=>$value)
 {
  $html .= '<tr><td bgcolor="#FFFFFF"><div style="padding:3px;color:#6E6E6E;">'.$value['sql'].'</div></td>';
  $html .= '<td align="center" bgcolor="#FFFFFF"><div style="padding:3px;color:#000000;">'.$value["count"].'</div></td></tr>';
 }
 $html.= "</table>";
 $html.= "</td></tr></table>";
 return $html;
 }
 function conn_status()
 {
 if(!$this->conn) return false;
 return true;
 }
}