<?php
	
	if(isset($_GET['id'])){
		if(isset($_GET['action'])){
			if($_GET['action']=='settings'&&$_GET['id']=='me'){
				if($is_get){
					//get all possible settings
					
					global $db;
					
					$JSON["settings"]=getUserOptions(getUser()['id']);
					
				}else if($is_put){
					//update settings
					
					if(isset($_PUT['option_key'])&&isset($_PUT['value'])){
						global $db;
						
						
						if($_PUT['option_key'] == 'mail'){
							//TODO
						}
						
						$data=$db->doQueryWithArgs("SELECT id,input_data_type FROM user_meta_options WHERE option_key=?", array($_PUT['option_key']), "s");
						
						if(count($data)==1){
							
							$data=$data[0];
							
							$id=$data['id'];
							
							$validation=validateDynamicInput($_PUT['value'], $data['input_data_type']);
							$_PUT['value']=$validation[1];
							
							if($validation[0]){
								
								$cnt = $db->doQueryWithArgs("SELECT id FROM user_meta WHERE user_id=? AND user_meta_option_id = ?", array(getUser()['id'], $id), "ii");
									
								if(count($cnt) <= 0){
									
									$db->doQueryWithArgs("INSERT INTO user_meta(user_id, user_meta_option_id, value) VALUES (?,?,?)", array(getUser()['id'], $id, $_PUT['value']), "iis");
									
								}else{
									
									$db->doQueryWithArgs("UPDATE user_meta SET value = ? WHERE id = ?", array($_PUT['value'], $cnt[0]['id']), "si");									
									
								}
								
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
			
			if($_GET['id'] == 'me'){
				
				$user = getUser();
				
				$JSON["user"]=array_merge(array("id"=>$user['id'], "mail"=>$user['mail']),getUserOptions(getUser()['id'], false));
				
			}else{
				
				$JSON["user"]=array_merge(array("id"=>$_GET['id']),getUserOptions($_GET['id'], false));
				
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
			
			$query="SELECT id, mail, first_name, last_name FROM (SELECT users.id, users.mail, MAX(IF(user_meta_options.option_key='first_name',user_meta.value,'')) as first_name, MAX(IF(user_meta_options.option_key='last_name',user_meta.value,'')) as last_name FROM users LEFT JOIN user_meta ON users.id = user_meta.user_id LEFT JOIN user_meta_options ON user_meta.user_meta_option_id=user_meta_options.id) as query1 WHERE 0=0";
			
			$filters=getFilters();
			
			
			if($filters!=false){
				
				$users=array();
				
				$args=array();
				$types="";
				
				if(isset($filters['search'])){
					
					$query.=" AND (first_name LIKE ? OR last_name LIKE ?) ORDER BY first_name DESC LIMIT 10";
					$args[]=$filters['search']."%";
					$args[]=$filters['search']."%";
					$types.="ss";
				}else{
					
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
					
					if(isset($filters['items_per_page']) && isset($filters['page'])){
						$query.=" LIMIT ?,?";
						$args[]=(((int)$filters['items_per_page']) * ((int)$filters['page']));
						$args[]=$filters['items_per_page'];
						$types.="ii";
					}
					
				}
				
				if(!empty($args)){
					$userList=$db->doQueryWithArgs($query, $args, $types);
					
				}else{
					addError(getMessages()->ERROR_API_USER_LIST_ALL);
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
	

function getUserOptions($user_id = null, $fields = true){
	
	global $db;
	
	$user_options = array();
	
	$value = !is_null($user_id);
	
	$data = $db->doQueryWithoutArgs("SELECT * FROM user_meta_options ORDER BY id ASC");
	
	$u_mail = "";
	
	$values = array();
	
	if($value){
		$v_data = $db->doQueryWithArgs("SELECT users.mail, user_meta.value, user_meta_options.option_key FROM user_meta_options LEFT JOIN user_meta ON user_meta_options.id=user_meta.user_meta_option_id LEFT JOIN users ON user_meta.user_id=users.id WHERE users.id=? ORDER BY user_meta_options.id ASC", array($user_id), "i");
		
		foreach($v_data as $v){
			$values[$v['option_key']] = $v['value'];
		}
		
		$u_mail = $v_data[0]['mail'];
			
		unset($v_data);
	}
	
	if($fields){
		for($i=0;$i<count($data);$i++){
		
			$user_option = $data[$i];
			
			$user_options[]=array(
				'key'=>$user_option["option_key"],
				'input_data_type'=>$user_option["input_data_type"],
				'required'=>$user_option["required"],
				'options'=>translateOptions($user_option["options"]),
				'description'=>getMessages()->$user_option["description_translation_key"]
			);
			
			if($value){
				$user_options[($i)]['value'] = isset($values[$user_option["option_key"]]) ? $values[$user_option["option_key"]] : null;
			}
			
		}
	}else{
		$user_options = array();
		
		for($i=0;$i<count($data);$i++){
		
			$user_option = $data[$i];
			
			$user_options[$user_option["option_key"]] = isset($values[$user_option["option_key"]]) ? $values[$user_option["option_key"]] : null;
			
		}
	}
	
	return $user_options;
}

?>