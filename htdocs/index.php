<?php
	require 'load.php';
	
	$is_get=false;
	$is_post=false;
	$is_put=false;
	$is_delete=false;
	
	$CONTENT_TYPE="";
	$JSON=array("version"=>"1.0");
	
	if($_SERVER['REQUEST_METHOD'] == 'GET'){
		//get
		$is_get=true;
	}else if($_SERVER['REQUEST_METHOD'] == 'POST'){
		//create
		$is_post=true;
	}else if($_SERVER['REQUEST_METHOD'] == 'PUT'){
		//update
		$is_put=true;
		parse_str(file_get_contents("php://input"),$_PUT);
	}else if($_SERVER['REQUEST_METHOD'] == 'DELETE'){
		//remove
		$is_delete=true;
		parse_str(file_get_contents("php://input"),$_DELETE);
	}else{
		addError("Unknown request method!");
	}
	
	function getFilters(){
		$filter=explode("?", $_SERVER['REQUEST_URI']);
		if(count($filter)>1&&strpos($filter[1], 'filter')!==false){
			parse_str($filter[1], $filters);
			if(isset($filters['filters'])){
				return $filters['filters'];
			}
		}
		return false;
	}
	
	
	if(isset($_GET['type'])){
		if($_GET['type']=='user'){
			
			require_once CORE_DIR.'ajax/users.php';
			
		}else if($_GET['type']=='group'){
			
			require_once CORE_DIR.'ajax/groups.php';
			
		}else if($_GET['type']=='event'){
			
			require_once CORE_DIR.'ajax/events.php';
			
		}else if($_GET['type']=='subject'){
			
			require_once CORE_DIR.'ajax/subjects.php';
			
		}else if($_GET['type']=='mark'){
			
			require_once CORE_DIR.'ajax/marks.php';
			
		}else if($_GET['type']=='notification'){
			
			require_once CORE_DIR.'ajax/notifications.php';
			
		}
	}
	
	header('Content-Type: application/json');
	
	/*if(empty($CONTENT_TYPE)){
		header('Content-Type: application/json');
	}else{
		header('Content-Type: application/json+'.$CONTENT_TYPE);
	}*/
	
	$JSON["errors"]=getErrors();
	
	die(json_encode($JSON));
?>