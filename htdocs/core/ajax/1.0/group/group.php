<?php

if(isset($_GET['id']){
	
	$group_type_id=$db->doQueryWithArgs("SELECT group_type FROM v_groups WHERE group_id=?", array($_GET['id']), "i")[0]['group_type'];
	
	if(isset($_GET['action']){
		if($_GET['action']=='settings'){
			if($is_get){
				//get all possible settings
				
				//get requested group
				
				$admin=currentUserCan('manage_options', $_GET['id']);
				
				global $db;
				
				$JSON["settings"]=array();
				$group_options=getGroupOptions($group_type_id);
				/*specific group type options*/
				foreach($group_options as $group_option){
					if($admin || currentUserCan("manage_options_".$group_option["option_key"], $_GET['id'])){
						
						$JSON["settings"][]=array(
							'key'=>$group_option["option_key"],
							'input_type'=>$group_option["input_type"],
							'input_data_type'=>$group_option["input_data_type"],
							'options'=>$group_option["options"],
							'description'=>getMessages()->$group_option["description_translation_key"],
							'users'=>$users
						);
					}
				}
				
			}else if($is_put){
				//update settings
				if(isset($_PUT['option_key'])&&isset($_PUT['value'])){
					if(currentUserCan('manage_options', $_GET['id'])){
						global $db;
						$data=$db->doQueryWithArgs("SELECT id,input_data_type FROM group_type_options WHERE option_key=?", array($_PUT['option_key']), "s");
						if(count($data)==0){
							$id=$data[0]->id;
							
							//validate value
							$value=$data[0]->input_data_type;
							$valid=false;
							
							if($value=='number'){
								$valid=is_numeric($value);
							}else if($value=='text'){
								$valid=is_string($value);
								$value=htmlentities(stripslashes(strip_tags($value)));
							}else if($value=='boolean'){
								$value=(bool)$value;
							}
							
							if($valid){
								$db->doQueryWithArgs("REPLACE into group_options (group_type_option_id,value) values(?, ?)", array($id, $_PUT['value']), "i, s");
							}else{
								addError(getMessages()->ERROR_API_INVALID_INPUT);
							}
						}else{
							addError(getMessages()->ERROR_API_INVALID_INPUT);
						}
					}else{
						addError(getMessages()->ERROR_API_PRIVILEGES);
					}
				}else{
					addError(getMessages()->ERROR_API_REQUIRED_FIELDS);
				}
			}else{
				addError(getMessages()->ERROR_API_REQUIRED_FIELDS);
			}
		}else if($_GET['action']=='capabilities'){
			
			if(currentUserCan('manage_capabilities', $_GET['id'])){
				if($is_get){
					//get all capabilities
					
					$JSON["capabilities"]=array();
					$JSON["capabilities"][]=array(
						'key'=>'manage_capabilities',
						'description'=>getMessages()->GROUP_CAPABILITIES_MANAGE_OPTIONS,
						'users'=>getUsersWithCap($_GET['id'], 'manage_capabilities')
					);
					$JSON["capabilities"][]=array(
						'key'=>'manage_options',
						'description'=>getMessages()->GROUP_CAPABILITIES_MANAGE_CAPS,
						'users'=>getUsersWithCap($_GET['id'], 'manage_options')
					);
					$group_options=getGroupOptions($group_type_id);
					foreach($group_options as $group_option){
						$JSON["capabilities"][]=array(
							'key'=>'manage_options_'.$group_option["option_key"],
							'description'=>getMessages()->$group_option["description_translation_key"],
							'users'=>getUsersWithCap($_GET['id'], 'manage_options_'.$group_option["option_key"])
						);
					}
					
					$JSON["capabilities"][]=array(
						'key'=>'invite_users',
						'description'=>getMessages()->GROUP_CAPABILITIES_INVITE_USERS,
						'users'=>getUsersWithCap($_GET['id'], 'invite_users')
					);
					$JSON["capabilities"][]=array(
						'key'=>'create_events',
						'description'=>getMessages()->GROUP_CAPABILITIES_CREATE_EVENTS,
						'users'=>getUsersWithCap($_GET['id'], 'create_events')
					);
					/*TODO: capabilities for different event types */
					
					$JSON["capabilities"][]=array(
						'key'=>'create_subject',
						'description'=>getMessages()->GROUP_CAPABILITIES_CREATE_SUBJECTS,
						'users'=>getUsersWithCap($_GET['id'], 'create_subject')
					);
				
				}else {
					if(isset($_PUT['user_id'])&&isset($_PUT['capability'])){
						//check if he already has this capability
						$data=$db->doQueryWithArgs("SELECT COUNT(*) as count, group_relation.id as group_relation_id FROM group_capabilities LEFT JOIN group_relations ON group_capabilities.relation_id=group_relations.id WHERE group_relations.member_type=1 AND group_relations.member_id=? AND group_relations.group_id=? AND group_capabilities.capability=?", array($_PUT["user_id"], $_GET['id'], $_PUT['capability']), "iis");
						
						if($is_put){
							if($data[0]["count"]<=0){
								//add it
								$db->doQueryWithArgs("INSERT INTO group_capabilities(relation_id,capability) VALUES(?,?)", array($data[0]["relation_id"], $_PUT['capability']), "is");
								
							}else{
								addError(getMessages()->ERROR_GROUPS_CAPABILITY_ALREADY_ASSIGNED);
							}
						}else if($is_delete){
							if($data[0]["count"]>0){
								//remove it
								$db->doQueryWithArgs("DELETE FROM group_capabilities WHERE relation_id=? AND capability=?", array($data[0]["relation_id"], $_PUT['capability']), "is");
								
							}else{
								addError(getMessages()->ERROR_GROUPS_CAPABILITY_NOT_REMOVEABLE);
							}
						}else{
							addError(getMessages()->ERROR_API_REQUIRED_FIELDS);
						}
					}else{
						addError(getMessages()->ERROR_API_REQUIRED_FIELDS);
					}
				}
			}else{
				addError(getMessages()->ERROR_API_PRIVILEGES);
			}
		}else if($_GET['action']=='join'){
			//adds a user to a group
			if($is_put){
				if(isset($_PUT['member_type_id'])&&isset($_PUT['member_id'])){
					//check permissions
					global $db;
					
					if(use isUserAllowedToAddUserToGroup($_PUT['member_id'], $_GET['id'], $_PUT['member_type_id'])){
						//user adds himself
						
						//check if he's already a member of this group and if not add him
						if($db->doQueryWithArgs("SELECT COUNT(*) as count FROM group_relations WHERE member_id=?, group_id=?, member_type=?", array($_PUT['member_id'], $_GET['id'], $_PUT['member_type_id']), "iii")[0]['count']==0){
							//add relation
							$db->doQueryWithArgs("INSERT INTO group_relations(member_id, group_id, member_type) VALUES(?,?,?)", array($_PUT['member_id'], $_GET['id'], $_PUT['member_type_id']), "iii");
						}else{
							addError(getMessages()->ERROR_GROUPS_ALREADY_MEMBER);
						}
					}else{
						//check permissions in parent groups
						addError(getMessages()->ERROR_API_PRIVILEGES);
					}
				}else{
					addError(getMessages()->ERROR_API_REQUIRED_FIELDS);
				}
			}else{
				addError(getMessages()->ERROR_API_REQUIRED_FIELDS);
			}
		}else if($_GET['action']=='leave'){
			//removes a user from a group
			
		}else{
			addError(getMessages()->ERROR_API_REQUIRED_FIELDS);
		}
	}else{
		//get group informations
		
		
	}
}else{
	if($is_post){
		//create new group
		
	}else if($is_delete){
		if(isset($_DELETE['group_id']){
			//delete group
		}
	}else if($is_get){
		//list all groups [filters]
	}else{
		addError(getMessages()->ERROR_API_REQUIRED_FIELDS);
	}
}

function getGroupOptions($group_type_id){
	$group_options=$db->doQueryWithArgs("SELECT * FROM group_type_options WHERE group_type_id=?", array($group_type_id), "i");
	$group_options[]=array(
		'key'=>'invite_only'
		'input_type'=>'checkbox',
		'input_data_type'=>'bool',
		'options'=>null,
		'description'=>getMessages()->GROUP_OPTIONS_INVITE_ONLY_DESC;
	);
	return $group_options;
}

function getUsersWithCap($group_id, $capability){
	
	/*TODO: Delete / Create Temp Table */
	
	$users=$db->doQueryWithArgs("SELECT users.id, user_meta.first_name, user_meta.last_name FROM group_capabilities LEFT JOIN group_relations ON group_capabilities.relation_id=group_relations.id LEFT JOIN users ON group_relations.member_id=users.id WHERE group_relations.member_type=1 AND group_relations.group_id=? AND group_capabilities.capability=?", array($group_id, $capability), "is");
	
	return $users;
}
	
?>