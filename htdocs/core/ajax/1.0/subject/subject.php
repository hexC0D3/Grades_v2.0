<?php

if(isset($_GET['id']){
	if(isset($_GET['action']){
		if($_GET['action']=='settings'){
			if($is_get){
				//get all possible settings
				
				$JSON["settings"]=array();
				$JSON["settings"][]=array(
					'setting'=>'name',
					'input_data_type'=>'text',
					'options'=>'{"input_type":"textfield"}',
					'description'=>getMessages()->SUBJECT_OPTIONS_NAME_DESC
				);
				
			}else if($is_put){
				//update settings
				
				global $db;
				
				$global_options=array(
					'name' => 'text'
				);
				
				if(in_array($_PUT['option_key'], array_keys($global_options))){
					$validation = validateDynamicInput($_PUT['value'], $global_options[$_PUT['option_key']]);
					$_PUT['value']=$validation[1];
					
					if($validation[0]){
						$db->doQueryWithArgs("REPLACE into subjects(id,".$_PUT['option_key'].") values(?, ?)", array($_GET['id'], $_PUT['value']), "is");
					}else{
						addError(getMessages()->ERROR_API_INVALID_INPUT);
					}
				}else{
					addError(getMessages()->ERROR_API_INVALID_INPUT);
				}
			}else{
				addError(getMessages()->ERROR_API_REQUIRED_FIELDS);
			}
		}else{
			addError(getMessages()->ERROR_API_REQUIRED_FIELDS);
		}
	}else{
		//get subject informations
		global $db;
		
		$data = $db->doQueryWithArgs("SELECT group_id FROM group_relations WHERE member_id=? AND member_type=4", array($_GET['id']), "i");
		
		if(count($data) == 1){
			if(isUserMemberOf($data[0]["group_id"])){
				$data = $db->doQueryWithArgs("SELECT id,name FROM subjects WHERE id=?", array($_GET['id']), "i");
				$JSON['subject']=$data[0];
			}else{
				addError(getMessages()->ERROR_API_PRIVILEGES);
			}
		}else{
			addError(getMessages()->UNKNOWN_ERROR(12));
		}
	}
}else{
	if($is_post){
		//create new subject
		
		if(isset($_POST['subject_name']) && isset($_POST['parent_group_id'])){
			if(currentUserCan("add_members", $_POST['parent_group_id'])){
				$data = $db->doQueryWithoutArgs("SHOW TABLE STATUS LIKE 'subjects'");
				$id = $data[0]['Auto_increment'];
				
				
				$db->doQueryWithArgs("INSERT INTO subjects(id, name) VALUES(?,?)", array($id, $_POST['subject_name']), "is");
				$db->doQueryWithArgs("INSERT INTO group_relations(member_id,group_id,member_type) VALUES(?,?,4)", array($id,$_POST['parent_group_id']), "ii");
				
			}else{
				addError(getMessages()->ERROR_API_PRIVILEGES);
			}
		}else{
			addError(getMessages()->ERROR_API_REQUIRED_FIELDS);
		}
	}else if($is_delete){
		if(isset($_DELETE['subject_id']){
			//delete subject
			
			//get parent group id
			
			global $db;
			$data = $db->doQueryWithArgs("SELECT group_id FROM group_relations WHERE member_id=? AND member_type=4", array($_DELETE['subject_id']), "i");
			if(count($data)==1){
				if(currentUserCan("manage_options", $data[0]["group_id"])){
					deleteSubject($_DELETE['subject_id']);
				}else{
					addError(getMessages()->ERROR_API_PRIVILEGES);
				}
			}else{
				addError(getMessages()->ERROR_API_INVALID_INPUT);
			}
		}
	}else if($is_get){
		//list all subjects [filters]
		
		global $db;
		
		$data = $db->doQueryWithArgs("SELECT group_id FROM group_relations WHERE member_id=? AND member_type=4", array($_GET['id']), "i");
		
		if(count($data) == 1){
			if(isUserMemberOf($data[0]["group_id"])){
				
				$filters=getFilters();
		
				if($filters!=false){
					
					$query="SELECT subjects.id,subjects.name FROM subjects WHERE 1=1";
				
					$groups=array();
					$args=array();
					$types="";
					
					if(isset($filters['group_parent_id'])){
							
						$query="SELECT subjects.id,subjects.name FROM (".
							"SELECT * from group_relations LEFT JOIN subjects ON group_relations.member_id=subjects.id WHERE group_relations.group_id=? AND member_type=4".
						") WHERE 1=1";
						$args[]=$filters['group_parent_id'];
						$type.="i";
						
					}
					
					if(isset($filters['name'])){
						$query.=" AND subjects.name LIKE ?";
						$args[]="%".$filters['name']."%";
						$types.="s";
					}
					
					if(isset($filters['items_per_page']) && isset($filters['page'])){
						$query.=" LIMIT ?,?";
						$args[]=$filters['items_per_page'];
						$args[]=(((int)$filters['items_per_page']) * ((int)$filters['page']));
						$types.="ii";
					}
					
					if(!empty($args)){
						$subjects=$db->doQueryWithArgs($query, $args, $types);
					}else{
						addError(getMessages()->ERROR_API_EVENTS_LIST_ALL);
					}
					
					$JSON["subjects"]=$subjects;
					
				}else{
					addError(getMessages()->ERROR_API_EVENTS_LIST_ALL);
				}
				
			}else{
				addError(getMessages()->ERROR_API_PRIVILEGES);
			}
		}else{
			addError(getMessages()->UNKNOWN_ERROR(13));
		}

	}else{
		addError(getMessages()->ERROR_API_REQUIRED_FIELDS);
	}
}
	
?>