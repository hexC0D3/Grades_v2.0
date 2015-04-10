<?php

if(isset($_GET['id']){
	
	$group_type_id = -1;
	
	$data=$db->doQueryWithArgs("SELECT type_id FROM groups WHERE id=?", array($_GET['id']), "i");
	if(count($data) == 1){
		$group_type_id = $data[0]['type_id'];
	}
	
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
							'input_data_type'=>$group_option["input_data_type"],
							'required'=>$group_option["required"],
							'options'=>$group_option["options"],
							'description'=>getMessages()->$group_option["description_translation_key"]
						);
					}
				}
				
			}else if($is_put){
				//update settings
				if(isset($_PUT['option_key'])&&isset($_PUT['value'])){
					if(currentUserCan('manage_options', $_GET['id']) || currentUserCan('manage_options_'.$_PUT['option_key'], $_GET['id'])){
						global $db;
						
						$global_options = array(
							'invite_only' => 'boolean',
							'name' => 'text'
						);
						
						if(in_array($_PUT['option_key'], array_keys($global_options)){
							$validation=validateDynamicInput($_PUT['value'], $global_options[$_PUT['option_key']]);
							$_PUT['value']=$validation[1];
							
							if($validation[0]){
								$db->doQueryWithArgs("REPLACE into groups (id,".$_PUT['option_key'].") values(?, ?)", array($_GET['id'], $_PUT['value']), "is");
							}else{
								addError(getMessages()->ERROR_API_INVALID_INPUT);
							}
							
						}else{
							$data=$db->doQueryWithArgs("SELECT id,input_data_type FROM group_type_options WHERE option_key=?", array($_PUT['option_key']), "s");
							if(count($data)==1){
								$data=$data[0];
								
								$id=$data->id;
								
								$validation=validateDynamicInput($_PUT['value'], $data->input_data_type);
								$_PUT['value']=$validation[1];
								
								if($validation[0]){
									$db->doQueryWithArgs("REPLACE into group_options (group_id,group_type_option_id,value) values(?, ?, ?)", array($_GET['id'], $id, $_PUT['value']), "iis");
								}else{
									addError(getMessages()->ERROR_API_INVALID_INPUT);
								}
							}else{
								addError(getMessages()->ERROR_API_INVALID_INPUT);
							}
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
						'key'=>'add_members',
						'description'=>getMessages()->GROUP_CAPABILITIES_ADD_MEMBERS,
						'users'=>getUsersWithCap($_GET['id'], 'add_members')
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
					
					if($_PUT['member_id'] == getUser()['id'] && $_PUT['member_type_id'] == 1){
						//user wants to add himself, check if groups is invite only
						
						global $db;
						$group = $db->doQueryWithArgs("SELECT invite_only FROM groups WHERE id=?", array($_GET['id']), "i");
						
						if(count($group)==1){
							if(($group[0]["invite_only"] == 0 || $group[0]["invite_only"] == "0") || currentUserCan('add_members', $_GET['id'])){
								if($db->doQueryWithArgs("SELECT COUNT(*) as count FROM group_relations WHERE member_id=?, group_id=?, member_type=?", array(getUser()['id'], $_GET['id'], 1), "iii")[0]['count']==0){
									//add relation
									$db->doQueryWithArgs("INSERT INTO group_relations(member_id, group_id, member_type) VALUES(?,?,?)", array(getUser()['id'], $_GET['id'], 1), "iii");
								}else{
									addError(getMessages()->ERROR_GROUPS_ALREADY_MEMBER);
								}
								
							}else{
								addError(getMessages()->ERROR_GROUPS_INVITE_ONLY);
							}
						}else{
							addError(getMessages()->ERROR_API_INVALID_INPUT);
						}
					}else{
						//user wants to add someone or someting else
						if(currentUserCan('add_members', $_GET['id'])){
							
							if($db->doQueryWithArgs("SELECT COUNT(*) as count FROM group_relations WHERE member_id=?, group_id=?, member_type=?", array($_PUT['member_id'], $_GET['id'], $_PUT['member_type_id']), "iii")[0]['count']==0){
								//add relation
								$db->doQueryWithArgs("INSERT INTO group_relations(member_id, group_id, member_type) VALUES(?,?,?)", array($_PUT['member_id'], $_GET['id'], $_PUT['member_type_id']), "iii");
							}else{
								addError(getMessages()->ERROR_GROUPS_ALREADY_MEMBER);
							}
							
						}else{
							addError(getMessages()->ERROR_API_PRIVILEGES);
						}
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
		
		global $db;
		$groups = $db->doQueryWithArgs("SELECT groups.id, groups.name, groups.invite_only, group_types.title as group_type  FROM groups LEFT JOIN group_types ON groups.type_id=group_types.id WHERE groups.id = ?", array($_GET['id']), "i");

		if(count($groups) == 1){
			$JSON['group']=$groups[0];
		}else{
			addError(getMessages()->ERROR_API_INVALID_INPUT);
		}
		
	}
}else{
	if($is_post){
		//create new group
		
		if(isset($_POST['group_name']) && isset($_POST['group_type_id']) && isset($_POST['parent_group_id']) && isset($_POST['invite_only']) && isset($_POST['options'])){
			//get all fields required by the desired type and check if they're sent
			
			if(is_array($_POST['options'])){
				global $db;
				$data = $db->doQueryWithArgs("SELECT id, option_key, input_data_type FROM group_type_options WHERE required = 1 AND group_type_id=?", array($_POST['group_type_id']), "i");
				
				$all_fields = true;
				
				$options = array();
				
				foreach($data as $val){
					if($all_fields && array_key_exists($val['option_key'], $_POST['options'])){
						$validation = validateDynamicInput($_POST['options'][$val['option_key']], $val['input_data_type']);
						if($validation[0]){
							$options[$val['id']] = $validation[1]
						}else{
							$all_fields=false;
							addError(getMessages()->ERROR_API_INVALID_INPUT);
						}
					}else{
						$all_fields=false;
					}
				}
				
				if($all_fields){
					
					//check if name already exists in combination with this type
					$count = $db->doQueryWithArgs("SELECT COUNT(*) as count FROM groups WHERE name=? AND type_id=?", array($_POST['group_name'],$_POST['group_type_id']), "si");
					$count = $count[0]["count"];
					
					if($count == 0){
						//create group
						
						$data = $db->doQueryWithoutArgs("SHOW TABLE STATUS LIKE 'groups'");
						$id = $data[0]['Auto_increment'];
					
						$db->doQueryWithArgs("INSERT INTO groups(id,name,invite_only,type_id) VALUES(?,?,?,?)", array($id,$_POST['group_name'], ((int)$_POST['invite_only']), $_POST['group_type_id']), "sii");
							
						$admin_user_id = getUser()['id'];
						
						$qm=array();
						$values=array();
						$types="";
						foreach($options as $group_type_option_id => $option){
							$qm[]="(?,?,?)";
							$values[]=$id;
							$values[]=$group_type_option_id;
							$values[]=$option;
							$types.="iis";
							
							//special handling with admins
							if(in_array($group_type_option_id, array(2,5)){
								$admin_user_id = $option;
							}
						}
						
						$db->doQueryWithArgs("INSERT INTO group_options(group_id,group_type_option_id,value) VALUES ".implode(", ", $qm), $values, $types);
						
						//add admin user to group
						
						$db->doQueryWithArgs("INSERT INTO group_relations(member_id,group_id,member_type)", array($admin_user_id,$id,1), "iii");
						
						//get relation id
						$relation_id = $db->doQueryWithArgs("SELECT id FROM group_relations WHERE member_id=? AND group_id=? AND member_type=?", array($admin_user_id,$id,1), "iii");
						
						if(count($relation_id)==1){
							$relation_id=$relation_id[0]['id'];
							
							//give him all capabilites
							
							$qm = array();
							$types = "";
							$values = array();
							$capabilities = array('manage_capabilities', 'manage_options', 'add_members', 'create_events', 'create_subject');
							
							foreach($capabilities as $capability){
								$qm[]="(?,?)";
								$values[]=$relation_id;
								$values[]=$capability;
								$types.="is";
							}
							
							$db->doQueryWithArgs("INSERT INTO group_capabilities(relation_id,capability) VALUES ".implode(", ", $qm), $values, $types);
							
							if($_POST['parent_group_id']>0){
								//set the parent of this group, but first check if parent group exists
							
								$data = $db->doQueryWithArgs("SELECT invite_only FROM groups WHERE id=?", array($_POST['parent_group_id']), "i");
								
								if(count($data)==1){
									
									//check if user is allowed to create a sub-group if it's invite only
									
									if($data[0]['invite_only'] == true){
										if(currentUserCan('add_members', $_POST['parent_group_id'])){
											$db->doQueryWithArgs("INSERT INTO group_relations(member_id,group_id,member_type) VALUES(?,?,2)", array($id,$_POST['parent_group_id']), "ii");
										}else{
											addError(getMessages()->ERROR_API_PRIVILEGES);
										}
									}else{
										$db->doQueryWithArgs("INSERT INTO group_relations(member_id,group_id,member_type) VALUES(?,?,2)", array($id,$_POST['parent_group_id']), "ii");
									}
									
									#done creating a group puh..
								}else{
									addError(getMessages()->ERROR_GROUPS_PARENT_NOT_EXISTING);
								}
							}else{
								//no error here
							}
							
						}else{
							addError(getMessages()->UNKNOWN_ERROR(5));
						}
					}else{
						addError(getMessages()->ERROR_GROUPS_ALREADY_EXISTS);
					}
				}else{
					addError(getMessages()->ERROR_API_REQUIRED_FIELDS);
				}
			}else{
				addError(getMessages()->ERROR_API_INVALID_INPUT);
			}
		}
		
	}else if($is_delete){
		if(isset($_DELETE['group_id']){
			if(currentUserCan('manage_options', $_DELETE['group_id']){
				//delete recursive everything related to this group
				deleteGroup($_DELETE['group_id']);
			}
		}
	}else if($is_get){
		//list groups [filters]
		
		global $db;
		
		$filters=getFilters();
		
		
		if($filters!=false){
			
			$groups=array();
			
			$args=array();
			$types="";
			
			$query="SELECT groups.id,groups.name FROM groups LEFT JOIN group_types ON groups.type_id=group_types.id WHERE 1=1";
			
			if(isset($filters['group_parent_id'])){
				
				$query="SELECT groups.id,groups.name FROM (".
					"SELECT * from group_relations LEFT JOIN groups ON group_relations.member_id=groups.id WHERE group_relations.group_id=? AND member_type=2".
				") LEFT JOIN group_types ON groups.type_id=group_types.id WHERE 1=1";
				$args[]=$filters['group_parent_id'];
				$type.="i";
				
			}
			
			if(isset($filters['name'])){
				$query.=" AND groups.name LIKE ?";
				$args[]="%".$filters['name']."%";
				$types.="s";
			}
			if(isset($filters['group_type_id'])){
				$query.=" AND groups.type_id = ?";
				$args[]=$filters['group_type_id'];
				$types.="s";
			}
			
			if(isset($filters['items_per_page']) && isset($filters['page'])){
				$query.=" LIMIT ?,?";
				$args[]=$filters['items_per_page'];
				$args[]=(((int)$filters['items_per_page']) * ((int)$filters['page']));
				$types.="ii";
			}
			
			if(!empty($args)){
				$groups=$db->doQueryWithArgs($query, $args, $types);
			}else{
				addError(getMessages()->ERROR_API_GROUPS_LIST_ALL);
			}
			
			$JSON["groups"]=$groups;
			
		}else{
			addError(getMessages()->ERROR_API_GROUPS_LIST_ALL);
		}
		
	}else{
		addError(getMessages()->ERROR_API_REQUIRED_FIELDS);
	}
}

function getGroupOptions($group_type_id){
	$group_options=$db->doQueryWithArgs("SELECT * FROM group_type_options WHERE group_type_id=?", array($group_type_id), "i");
	$group_options[]=array(
		'key'=>'invite_only'
		'input_data_type'=>'boolean',
		'options'=>'{"input_type":"checkbox"}',
		'description'=>getMessages()->GROUP_OPTIONS_INVITE_ONLY_DESC;
	);
	$group_options[]=array(
		'key'=>'name'
		'input_data_type'=>'text',
		'options'=>'{"input_type":"textfield"}',
		'description'=>getMessages()->GROUP_OPTIONS_NAME_DESC;
	);
	return $group_options;
}

function getUsersWithCap($group_id, $capability){
	
	/*TODO: Delete / Create Temp Table */
	
	$users=$db->doQueryWithArgs("SELECT users.id, user_meta.first_name, user_meta.last_name FROM group_capabilities LEFT JOIN group_relations ON group_capabilities.relation_id=group_relations.id LEFT JOIN users ON group_relations.member_id=users.id WHERE group_relations.member_type=1 AND group_relations.group_id=? AND group_capabilities.capability=?", array($group_id, $capability), "is");
	
	return $users;
}
	
?>