<?php
	
	if(isset($_GET['id'])){
		if(isset($_GET['action'])){
			if($_GET['action']=='settings'&&$_GET['id']=='me'){
				if($is_get){
					//get all possible settings
					
				}else if($is_put){
					//update settings
				}
			}
		}else{
			//get user informations
			
			$CONTENT_TYPE="user";
			global $db;
			
			$user_data=$db->doQueryWithArgs("SELECT users.id, user_meta.first_name, user_meta.last_name, user_meta.birthday, user_meta.about FROM users LEFT JOIN user_meta ON users.id=user_meta.user_id WHERE users.id=?", array($_GET['id']), "i");
			
			if(count($user_data)==1){
				$user_data=$user_data[0];
			
				$user_data["actions"]=array();
				
				$JSON["user"]=$user_data;
			}
			
		}
	}else{
		
		$CONTENT_TYPE="users";
		
		global $db;
		
		$query="SELECT users.id, user_meta.first_name, user_meta.last_name from users LEFT JOIN user_meta ON users.id = user_meta.user_id WHERE 1=1";
		
		$users=array();
		
		$filters=getFilters();
		if($filters!=false){
			
			$args=array();
			$types="";
			
			if(isset($filters['name'])){
				$query.=" AND concat(user_meta.first_name, ' ', user_meta.last_name) LIKE ?";
				$args[]="%".$filters['name']."%";
				$types.="s";
			}
			if(isset($filters['mail'])){
				$query.=" AND users.mail=?";
				$args[]=$filters['mail'];
				$types.="s";
			}
			
			if(empty($args)){
				$userList=$db->doQueryWithoutArgs($query);
			}else{
				$userList=$db->doQueryWithArgs($query, $args, $types);
			}
			
			if(isset($filters['group_id'])){
				foreach($userList as $key => $userData){
					$user=$userData;
					if(false/*is user in group*/){
						$user['actions']=array("method"=>"GET", "action"=>"/user/".$userData['id']);
						$users[]=$user;
					}
				}
			}else{
				foreach($userList as $key => $userData){
					$user=$userData;
					$user['actions']=array("method"=>"GET", "action"=>"/user/".$userData['id']);
					
					$users[]=$user;
				}
			}
			
		}else{
			
			if($is_post){
				//register a new user
				if(isset($_POST['mail'])&&isset($_POST['captcha'])){
					registerUser($_POST['mail'], $_POST['captcha']);
				}
			}else{
				addError(getMessages()->ERROR_API_USER_LIST_ALL);
			}
			
		}
		
		$JSON["users"]=$users;
	}	
?>