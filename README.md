# multi-db-sanatizer
This can sanatize all your dbs at once you just need to provide database connections and provide tags you need to find.This will find all the content within your mentions tags and preview the result where you could take the action


	private $database_arr = array(
		array(
		   'host'    =>'hostname',
		   'database'=>'databasename',
		   'db_user' =>'database_user',
		   'db_pass' =>'database_password'
		),
		array(
		   'host'    =>'hostname',
		   'database'=>'databasename',
		   'db_user' =>'database_user',
		   'db_pass' =>'database_password'
		)
	);//Database connections
	
	Note: Remote access and read,write and delete permissions must be given to the database user

	private $project_data = [
	'db1'=>'Project1',
	'db2'=>'Project2',
	'db3'=>'Project3'
	];
	//contains projects..
	Note: You can define project names here against database names
	
	
	private $tags = ['<script','<iframe','<style'];//contains the tags to find..
	Note: use "<" before tag
	
	
	private $datatypes = ['text','longtext'];
	Note: contains datatypes to find, we need to specify the datatypes so that we have less amount of columns to sanatize as these sql injections are mostly found in text,longtext or midiumtext datatypes
	
	

	Access index.php to trigger the program found data will be shown in a table tag wise you can either delete all or individually remove of a specific data within the found the tag
