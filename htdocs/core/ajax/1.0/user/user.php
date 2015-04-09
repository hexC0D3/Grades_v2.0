<?php
	
	if(isset($_GET['id'])){
		if(isset($_GET['action'])){
			if($_GET['action']=='settings'&&$_GET['id']=='me'){
				if($is_get){
					//get all possible settings
					
					global $db
					
					$JSON["settings"]=array();
					
					$metas=$db->doQueryWithArgs("SELECT user_meta.value,user_meta_options.* FROM user_meta LEFT JOIN user_meta_options ON user_meta.user_meta_option_id = user_meta_options.id WHERE user_meta.user_id=?", array(getUser()['id']), "i");
					
					foreach($metas as $meta){
						$JSON["settings"][]=array(
							'setting'=>$meta["option_key"],
							'input_data_type'=>$meta["input_data_type"],
							'options'=>$meta["options"],
							'description'=>getMessages()->$meta["description_translation_key"]
						);
					}
					
				}else if($is_put){
					//update settings
					
					if(isset($_PUT['setting'])&&isset($_PUT['value'])){
						global $db;
						
						$data=$db->doQueryWithArgs("SELECT id,input_data_type FROM user_meta_options WHERE option_key=?", array($_PUT['setting']), "s");
						
						if(count($data)==0){
							$data=$data[0];
							
							$id=$data->id;
							
							$validation=validateDynamicInput($_PUT['value'], $data->input_data_type);
							$_PUT['value']=$validation[1];
							
							if($validation[0]){
								$db->doQueryWithArgs("REPLACE into group_options (group_type_option_id,value) values(?, ?)", array($id, $_PUT['value']), "is");
							}else{
								addError(getMessages()->ERROR_API_INVALID_INPUT);
							}
							
						}else{
							addError(getMessages()->ERROR_API_INVALID_INPUT);
						}
					}else{
						addError(getMessages()->ERROR_API_INVALID_INPUT);
					}
				}else{
					addError(getMessages()->ERROR_API_REQUIRED_FIELDS);
				}
			}else if($_GET['action']=='verify'||$_GET['action']=='reset_pw'){
				if($is_post){
					if(filter_var($_GET['id'], FILTER_VALIDATE_EMAIL)){
						$mail=$_GET['id'];
						
						if(isset($_POST['code'])&&isset($_POST['password'])){
							//try to set a password for a user
							resetPassword($mail, $_POST['code'], $_POST['password']);
							
						}else{
							addError(getMessages()->ERROR_API_REQUIRED_FIELDS);
						}
					}else{
						addError(getMessages()->ERROR_MAIL_INVALID);
					}
				}else{
					addError(getMessages()->ERROR_API_REQUIRED_FIELDS);
				}
			}else if($_GET['action']=='login'){
				if($is_post){
					if(filter_var($_GET['id'], FILTER_VALIDATE_EMAIL)){
						$mail=$_GET['id'];
						if(isset($_POST['password'])){
							logInUser($mail, $_POST['password']);
						}else{
							addError(getMessages()->ERROR_API_REQUIRED_FIELDS);
						}
					}else{
						addError(getMessages()->ERROR_MAIL_INVALID);
					}
				}else{
					addError(getMessages()->ERROR_API_REQUIRED_FIELDS);
				}
			}
		}else{
			//get user informations
			
			global $db;
				
			$user_data=$db->doQueryWithArgs("SELECT users.id, user_meta.first_name, user_meta.last_name, user_meta.birthday, user_meta.about FROM users LEFT JOIN user_meta ON users.id=user_meta.user_id WHERE users.id=?", array($_GET['id']), "i");
			
			if(count($groups) == 1){
				$JSON["user"]=$user_data[0];
			}else{
				addError(getMessages()->ERROR_API_INVALID_INPUT);
			}
			
		}
	}else{
		
		if($is_post){
				
			//register a new user
			if(isset($_POST['mail'])&&isset($_POST['captcha'])){
				registerUser($_POST['mail'], $_POST['captcha']);
			}
		}else{
			global $db;
			
			$query="SELECT users.id, user_meta.first_name, user_meta.last_name from users LEFT JOIN user_meta ON users.id = user_meta.user_id WHERE 1=1";
			
			$filters=getFilters();
			
			
			if($filters!=false){
				
				$users=array();
				
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
						
						$data = $db->doQueryWithArgs("SELECT COUNT(*) as count FROM group_relations WHERE member_id=? AND member_type_id=1", array($user['id']), "i");
						
						if(count($data) > 0){
							$users[]=$user;
						}
					}
				}else{
					foreach($userList as $key => $userData){
						$user=$userData;
						
						$users[]=$user;
					}
				}
				
				$JSON["users"]=$users;
				
			}else{
				addError(getMessages()->ERROR_API_USER_LIST_ALL);
			}
		}
		
	}	
?>