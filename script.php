<?php 
error_reporting(0);
ini_set('display_errors', 0);

/**
 * @author  Mohit mehrotra
 * @class   Script
 * @detail  This class is used to sanatize data within multiple datbases
 */
 
class Script{
	
	private $database_arr = array(
		array(
		   'host'    =>'hostname',
		   'database'=>'database_name1',
		   'db_user' =>'username',
		   'db_pass' =>'password'
		),
		array(
		   'host'    =>'hostname',
		   'database'=>'database_name2',
		   'db_user' =>'username',
		   'db_pass' =>'password'
		)
	);//Database connections

	private $conn_arr = [];//database connection strings
	
	private $primary_keys = [];//Collects the primary key columns
	
	private $tables = [];//collects the tables database wise
	
	private $table_columns = [];//collects database columns table and database wise
	
	private $tag_data = [];//collects the tags
	
	private $project_data = [
	'database_name1'=>'Project1',
	'database_name2'=>'Project2'
	];//contains projects irrespective to database name.. please use database name given above $database_arr eg database_name1
	
	private $tags = ['<script','<iframe','<style'];//contains the tags to find
	
	private $datatypes = ['text','longtext'];//contains datatypes to find, we need to specify the datatypes so that we have less amount of columns to sanatize as these sql injections are mostly found in text,longtext or midiumtext datatypes
	
	private static $instance = null;//contains the instances of this class
	
	
	/**
     * This function is to get class instance one time only so that we could achieve singleton pattern
     *
     */
	public static function getInstance() {
        if(!self::$instance) { // If no instance then make one
            self::$instance = new self();
        }
        return self::$instance;
	}
	
	
	 /**
     * Private Constructor
     *
     */	
	private function __construct(){
		if(!empty($this->database_arr)){
			foreach($this->database_arr as $key=>$value){
				
				$conn = mysqli_connect($value['host'],$value['db_user'],$value['db_pass'],$value['database']);
				if (mysqli_connect_errno()){
				  $this->make_log("Failed to connect to Database - ".$value['database'].": " . mysqli_connect_error());
				 }
				else{
				   $this->conn_arr[$value['database']] = $conn;	
				 }
			}
	
		}		
	}
	
	
	/**
     * This function is to search data within multiple databases and tables
     * @return array
     */
	public function searchData($tag){
		set_time_limit(0);
		$tag = html_entity_decode($tag);
		$tables = $this->tables;
		$data = [];
		
		 if(!empty($tables)){
				$i = 0;//to get index of connection string
				
				foreach($tables as $keys=>$val){
					if(!empty($val)){
						$val = array_unique($val);
						$sql_search_fields = [];
						foreach($val as $table){
							$res = !empty($this->table_columns[$keys][$table])?$this->table_columns[$keys][$table]:false;
							$sql_search = "";
							$sql_search_fields = [];
							if($res){
								$column = "";
								$sql_search = "select * from ".$table." where ";
								foreach($res as $column){
								$sql_search_fields[] = $column." like('%".$tag."%')";
								}
								
								$sql_search .= implode(" OR ", $sql_search_fields);
								
							}
							
							if(!empty($sql_search)){
								$result = $this->getResult($sql_search,$this->conn_arr[$keys]);
								if(!empty($result)){
									$data[$keys][$table] = $result;
								}
							}
							
							usleep(20000);
						}
					}
					$i++;
					sleep(1);
					
				}
				
				
			}
			return $data;	
			
		 
	}
	
	/**
     * This function is to make log date wise
     * @return void
     * 
     * @param $log_msg
     */
	private function make_log($log_msg){
			$log_filename = "logs";
			if (!file_exists($log_filename)){
				mkdir($log_filename, 0777, true);
			}
			
			$log_file_data = $log_filename.'/'.'log_' . date('d-M-Y') . '.log';
			
			file_put_contents($log_file_data, date("d-m-Y h:i:s a")."\n".$log_msg . "\n\n", FILE_APPEND);
	}
		
	
	/**
     * This function is to strip tag from string
     * @return string
     * 
     * @param $html
     * @param $tag
     *
     */
	public function stripBlacklistTag($html, $tag) {
		$regex = '#<\s*' . $tag . '[^>]*>.*?<\s*/\s*'. $tag . '>#msi';
        $html = preg_replace($regex, '[removed]', $html);
		return $html;
	}
	
	/**
     * This function is to strip tag from string
     * @return string
     * 
     * @param $html
     * @param $tag
     *
     */
	public function matchBlacklistTag($html,$column,$tag) {
		$regex = '#<\s*' . $tag . '[^>]*>.*?<\s*/\s*'. $tag . '>#msi';
        preg_match_all($regex, $html, $matches);
		if($matches){
			foreach ($matches[0] as $match) {
				echo htmlspecialchars($match);
			}
		}
	
	}
	
	/**
     * This function is to get tag column from string
     * @return string
     * 
     * @param $html
     * @param $tag
     *
     */
	public function matchBlacklistTagsCol($html,$column,$tag) {
		$regex = '#<\s*' . $tag . '[^>]*>.*?<\s*/\s*'. $tag . '>#msi';
        preg_match($regex, $html, $matches);
		if($matches){
			return $column;
		}
		else{
			return false;
			}
	
	}
	
	/**
     * This function is collect tables of all databases
     * @return void
     */
	
	public function collectData(){
		if(!empty($this->conn_arr)){
			foreach($this->conn_arr as $key => $conn){
				$tables[$key] = $this->getTables($conn);
			}
			
			if(!empty($tables)){
				$this->tables = $tables;
				$i = 0;//to get index of connection string
				foreach($tables as $keys=>$val){
					if(!empty($val)){
						
						
						foreach($val as $table){
							
							$res = $this->getTablePrimaryKeyName($table,$this->conn_arr[$keys]);
							if($res){
							
							$this->primary_keys[$keys][$table] = $res;
							
							}
							
							$res = $this->getTableColumns($keys,$table,$this->conn_arr[$keys]);
							if(!empty($res)){
							
							$this->table_columns[$keys][$table] = $res;
							
							}
							
						}
					}
					$i++;
						
				}	
				
			}
			
			foreach($this->tags as $v){
				$result_set = $this->searchData($v);
				if(!empty($result_set)){
					$v = str_replace("<","",$v);
					$this->tag_data[$v] = $result_set;	
				}
			}
			
			$this->Loader();
		}
		
		
	}
	
	/**
     * This function is to get num_rows
     * @return int
     */
	private function numRows($result_obj){
		return $result_obj->num_rows;
	}
	
	/**
     * This function is to get connection string from an array
     * @return array
     */
	public function getConnectionString($db){
		return $this->conn_arr[$db];
	}
	
	/**
     * This function is to get primary key column of the table
     * @return bool
     */
	private function getTablePrimaryKeyName($table,$connection_string){
		$sql = "SHOW KEYS FROM ".$table." WHERE Key_name = 'PRIMARY' ";
		 $res = $connection_string->query($sql);
		
		 if($this->numRows($res)){
			while($row = $res->fetch_assoc()){
				
				return $row['Column_name'];
			}
		 }
		 return false;
	}

	/**
     * This function is to get databases
     * @return array
     */
	public function getDatabases(){
		
		 return $this->database_arr;;
	}

	/**
     * This function is to get tags
     * @return array
     */
	public function getTags(){
		
		 return $this->tags;
	}
	
	/**
     * This function is to get result
     * @return array
     */
	public function getResult($sql,$connection_string){
		$res = $connection_string->query($sql);
		
		 $arr = [];
		
		 if($this->numRows($res)){
			 
			while($row = $res->fetch_assoc()){
				$arr[] = $row;
			}
		 } 
		 return $arr;
	}
	
	/**
     * This function is to execute
     * @return array
     */
	public function executeQuery($sql,$connection_string){
		$connection_string->query($sql);
	}
	
	/**
     * This function is to get a single record 
     * @return array
     */
	public function getRow($sql,$connection_string){
		$res = $connection_string->query($sql);
		
		 $arr = [];
		
		 if($this->numRows($res)){
			 
			$arr = $res->fetch_object();
		 } 
		 return $arr;
	}
	
	/**
     * This function is to get array of tables of a specific database
     * @return array
     */
	private function getTables($connection_string){
		 $sql = "SHOW TABLES";
		 $res = $connection_string->query($sql);
		 $arr = [];
		 if($this->numRows($res)){
			while($row = $res->fetch_array()){
				$arr[] = $row[0];
			}
		 } 
		 return $arr;
	}
	
	/**
     * This function is to get table columns
     * @return array
     */
	private function getTableColumns($db,$table,$connection_string){
		$condition = "";
		
		if(!empty($this->datatypes)){
			$condition = 'AND ('; 
			foreach($this->datatypes as $k=>$v){
				if($k){
					$condition .= ' OR ';
				}
				$condition .= 'DATA_TYPE="'.$v.'" '; 
			}
			$condition .= ')'; 
			
			
		}
		 
		 $sql = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '".$db."' AND  TABLE_NAME = '".$table."' ".$condition."";
		 $res = $connection_string->query($sql);
		 $arr = [];
		 
		 if($this->numRows($res)){
			while($row = $res->fetch_array()){
				$arr[] = $row['COLUMN_NAME'];
			}
		 } 
		 
		 return $arr;
	}
	
	/**
     * This function is to get load found data 
     * @return void
     */
	private function Loader(){
		$data = $this->tag_data;
		$obj = self::getInstance();
		$projects = $this->project_data;
		require_once('data.php');
	}

}

