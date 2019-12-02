<?php 
	
	require_once('script.php');

	$obj = Script::getInstance();

	$table = $_GET['table'];
	$columns = $_GET['data'];
	$db = $_GET['db'];
	$key = $_GET['key'];
	$tag = $_GET['tag'];

	if(empty($table) || empty($columns) || empty($columns) || empty($columns)){
		die("Data not available!");	
	}
	
	$cols = json_decode($columns);
	$column = implode(",",$cols);
	$keys = explode("=>",$key);
	$key_string = implode("=",$keys);
	
	$query = "SELECT ".$column." FROM ".$table." WHERE ".$key_string.""; 
	
	$connection_string = $obj->getConnectionString($db);
	$data = $obj->getRow($query,$connection_string);
	
	if(!empty($data)){
		foreach($data as $k => $value){
			$sanatized_data = $obj->stripBlacklistTag($value,$tag);
			$sanatized_data = $connection_string->real_escape_string($sanatized_data);
	    	
	    	$sql = "UPDATE ".$table." SET ".$k."='".$sanatized_data."' WHERE ".$key_string."";
			
			$obj->executeQuery($sql,$connection_string);
			echo "success";
		}	
	}
	

