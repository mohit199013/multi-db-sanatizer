<!DOCTYPE html>
<html lang="en">
<head>
  <title>Sanatize database</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/js/bootstrap.min.js"></script>
</head>
<body>

<div class="container">
  <h2>Filtered Data</h2>
  <?php 
  echo "<div id='table_html'></div><br><br><hr>";
	if(!empty($data)){
		echo "Delete From Database: <select class='' id='db-select'> <option value=''>Select a db to delete all data</option>";
		$tags = [];
		
		foreach($obj->getDatabases() as $db_value){
			echo '<option value="'.$db_value["database"].'">'.$db_value["database"].'</option>';
			foreach($obj->getTags() as $tag_value){
				$tag_value = str_replace("<","",$tag_value);
				$tags[$db_value["database"]][$tag_value] = 0;
			}
		}
		echo "</select>";

		// echo "<pre>";
		// print_r($tags);

		
		echo '<a href="javascript:void(0)" class="delete-db btn btn-primary"> Delete All</a>';
		foreach($data as $key=>$value){
			echo "<h2 class='table-".$key."'>Tag: ".$key."</h2>";
			if(!empty($value)){
				?>
					<!-- <a href="javascript:void(0)" class="delete-all table-<?php echo $key; ?>" data-tag="<?php echo $key; ?>">Delete All</a> -->
					  <table class="table table-<?php echo $key; ?>">
						<thead>
						  <tr>
							<th>String</th>
							<th>Database</th>
							<th>Project</th>
							<th>Table</th>
							<th>Found</th>
							<th>ID</th>
							<th>Action</th>
						  </tr>
						</thead>
				<?php
				
				foreach($value as $k=>$vals){
					if(!empty($vals)){ 
						
						foreach($vals as $keys=>$table_columns){
							if(!empty($table_columns)){ 
								
								foreach($table_columns as $table_vals){
						?>
					
						<tbody>
						<tr>
							<td><?php echo $key; ?></td>
							<td><?php echo $k; ?></td>
							<td><b><?php echo $projects[$k]; ?></b></td>
							<td><?php echo $keys; ?></td>
							<td>
							<?php
							$tags[$k][$key] = $tags[$k][$key]+1;
							$cols = [];
							$primary_key = "id";
							$found_columns = [];
								$num = 0;
								foreach($table_vals as $col_keys=>$v){
									if(!$num){
										$primary_key = $col_keys."=>".$v;
									}
									if($obj->matchBlacklistTagsCol($v,$col_keys, $key)){
										$found_columns[] = $col_keys;		
									}
									$obj->matchBlacklistTag($v,$col_keys, $key);	
									$num++;
								}
							 ?>
							 </td>
							 <td><?php echo end(explode("=>",$primary_key)); ?></td>
							<td><a class="delete sanatize-<?php echo $key; ?> sanatize-db-<?php echo $k; ?>" href="javascript:void(0)" data-href='sanatize.php?table=<?php echo $keys; ?>&tag=<?php echo $key; ?>&db=<?php echo $k; ?>&key=<?php echo $primary_key; ?>&data=<?php echo json_encode($found_columns); ?>'>Sanatize Data</a></td>
						</tr>			
						</tbody>
					  	
				<?php	}
			}
			$counter++;
				
				}
			}
				}
				echo "</table>	";
			}
		}
		$c = 0;
		$table_html = "<h1>Tag Counts</h1>";
		$table_html .= "<table class='table'>";
		$table_html .= "<thead><tr><th>#</th><th>Database</th><th>Tag</th><th>Count</th></tr></thead><tbody>";
			foreach ($tags as $key => $value) {
				foreach ($value as $k => $val) {
				$c++;
				$table_html .= "<tr>
					<td>".$c."</td>
					<td><b>".$key."</b></td>
					<td><b>".$k."</b></td>
					<td><b>".$val."</b></td>
				</tr>";
			}
		}
		$table_html .= "</tbody></table>";
	}
	else{
		$table_html = "<h1>No Data Available!</h1>";
		echo "<h1>No Data Available!</h1>";
		}
		// echo "<pre>";
		// print_r($tags);
  ?>

</div>
<script>
$("body").on("click",".delete-all",function(){
	var tag = $(this).data("tag");
	$(".sanatize-"+tag).each(function(){
		var url = $(this).data("href");
		var ele = this;
		$.ajax({
        url: url,
        type: 'GET',
        success: function(res) {
		
            $(ele).parents("tr").fadeOut(300, function() { $(this).remove(); });
        }
    });
	});
	$(".table-"+tag).fadeOut(1000, function() { $(this).remove(); });
});

$("body").on("click",".delete",function(){
		var url = $(this).data("href");
		var ele = this;
		$.ajax({
        url: url,
        type: 'GET',
        success: function(res) {
		
            $(ele).parents("tr").fadeOut(300, function() { $(this).remove(); });
            
        }
    });
});
$("body").on("click",".delete-db",function(){
	var db = $("#db-select").val();
	if(db == ""){
		alert("Please select a db!");
		return false;
	}
		var r = confirm("Are you sure?");
			if (r == false) {
			  return false;
			} 
		
	$(".sanatize-db-"+db).each(function(){
		var url = $(this).data("href");
		var ele = this;
		$.ajax({
        url: url,
        type: 'GET',
        success: function(res) {
		
            $(ele).parents("tr").fadeOut(300, function() { $(this).remove(); });
        }
    });
	});
	$(".table-"+tag).fadeOut(1000, function() { $(this).remove(); });
});


$("#table_html").html(`<?php echo $table_html; ?>`);
</script>
</body>
</html>
