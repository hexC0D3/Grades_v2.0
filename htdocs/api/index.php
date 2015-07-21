<?php
	require '../load.php';
	
	$is_get=false;
	$is_post=false;
	$is_put=false;
	$is_delete=false;
	
	$versions=array("v1"=>"1.0");
	$version=false;
	
	if(isset($_GET['version'])&&isset($versions[$_GET['version']])){
		$version=$versions[$_GET['version']];
	}else{
		addError(getMessages()->ERROR_API_VERSIONS_INVALID);
		
		header('Content-Type: application/json');
		$JSON["errors"]=getErrors();
	
		die(json_encode($JSON));
	}
	
	global $JSON, $SESSION_TOKEN, $_PUT, $_DELETE, $lang;
	
	$JSON=array("version"=>$version, "lang"=>$lang);
	
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
		addError(getMessages()->ERROR_INVALID_REQUEST_METHOD);
	}
	
	function getPseudoGetParams($key){
		global $_PSEUDO_GET;
		if(!isset($_PSEUDO_GET)){
			
			$pseudo = explode("?", $_SERVER['REQUEST_URI']);
			
			if(count($pseudo) > 1){
				parse_str($pseudo[1], $_PSEUDO_GET);
			}
		}
		
		if(isset($_PSEUDO_GET[$key])){
			return $_PSEUDO_GET[$key];
		}
		
		return array();
	}
	
	function getFilters(){
		return getPseudoGetParams('filters');
	}
	
	$SESSION_TOKEN = getPseudoGetParams('session_token');
	
	//check if user is logged in or wants to login/register/reset_password, otherwise we block
	if(isUserLoggedIn() || ($_GET['type']=='user' && isset($_GET['action']) && in_array($_GET['action'], array('verify','reset_pw','login'))) || (!isset($_GET['action']) && $is_post && $_GET['type']=='user')){
		
		if(isset($_GET['type'])){
			if($_GET['type']=='user'){
				
				require_once CORE_DIR.'ajax/'.$version.'/user/user.php';
				
			}else if($_GET['type']=='group'){
				
				require_once CORE_DIR.'ajax/'.$version.'/group/group.php';
				
			}else if($_GET['type']=='event'){
				
				require_once CORE_DIR.'ajax/'.$version.'/event/event.php';
				
			}else if($_GET['type']=='subject'){
				
				require_once CORE_DIR.'ajax/'.$version.'/subject/subject.php';
				
			}else if($_GET['type']=='mark'){
				
				require_once CORE_DIR.'ajax/'.$version.'/mark/mark.php';
				
			}else if($_GET['type']=='notification'){
				
				require_once CORE_DIR.'ajax/'.$version.'/notification/notification.php';
				
			}
		}
		
	}else{
		addError(getMessages()->ERROR_API_PRIVILEGES);
	}
	
	
	header('Content-Type: application/json');
	
	$JSON["errors"]=getErrors();
	
	die(json_encode($JSON));
?>