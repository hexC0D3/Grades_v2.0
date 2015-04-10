<?php
	
if(isset($_GET['id']){
	
	$event_type_id = -1;
	
	$data=$db->doQueryWithArgs("SELECT type_id FROM events WHERE id=?", array($_GET['id']), "i");
	if(count($data) == 1){
		$event_type_id = $data[0]['type_id'];
	}
	
	if(isset($_GET['action']){
		if($_GET['action']=='settings'){
			if($is_get){
				//get all possible settings
				
				//get requested event
				
				global $db;
				
				$JSON["settings"]=array();
				$event_options=getEventOptions($event_type_id);
				/*specific event type options*/
				foreach($event_options as $event_option){
						
					$JSON["settings"][]=array(
						'key'=>$event_option["option_key"],
						'input_data_type'=>$event_option["input_data_type"],
						'required'=>$event_option["required"],
						'options'=>$event_option["options"],
						'description'=>getMessages()->$group_option["description_translation_key"]
					);
				}
				
			}else if($is_put){
				//update settings
				
				global $db;
				
				//get group this event is in
				$data = $db->doQueryWithArgs("SELECT group_id FROM group_relations WHERE member_id=? AND member_type=3", array($_GET['id']), "i");
				if(count($data)==1){
					$group_id = $data[0]["group_id"];
					
					if(currentUserCan("manage_options", $group_id)){
						
						if(isset($_PUT['option_key'])&&isset($_PUT['value'])){
							$global_options = array(
								'title' => 'text'
							);
							
							if(in_array($_PUT['option_key'], array_keys($global_options))){
								$validation = validateDynamicInput($_PUT['value'], $global_options[$_PUT['option_key']]);
								$_PUT['value']=$validation[1];
								
								if($validation[0]){
									$db->doQueryWithArgs("REPLACE into events (id,".$_PUT['option_key'].") values(?, ?)", array($_GET['id'], $_PUT['value']), "is");
								}else{
									addError(getMessages()->ERROR_API_INVALID_INPUT);
								}
							}else{
								$data=$db->doQueryWithArgs("SELECT id,input_data_type FROM event_type_options WHERE option_key=?", array($_PUT['option_key']), "s");
								if(count($data)==1){
									
									$id=$data[0]["id"];
									
									$validation = validateDynamicInput($_PUT['value'], $data[0]["input_data_type"]);
									$_PUT['value']=$validation[1];
									
									if($validation[0]){
										$db->doQueryWithArgs("REPLACE into event_options (event_id,event_type_option_id,value) values(?, ?, ?)", array($_GET['id'], $id, $_PUT['value']), "iis");
									}else{
										addError(getMessages()->ERROR_API_INVALID_INPUT);
									}
								}else{
									addError(getMessages()->ERROR_API_INVALID_INPUT);
								}
							}
						}else{
							addError(getMessages()->ERROR_API_REQUIRED_FIELDS);
						}
					}else{
						addError(getMessages()->ERROR_API_PRIVILEGES);
					}
				}else{
					addError(getMessages()->UNKNOWN_ERROR(6));
				}
			}else{
				addError(getMessages()->ERROR_API_REQUIRED_FIELDS);
			}
		}else{
			addError(getMessages()->ERROR_API_REQUIRED_FIELDS);
		}
	}else{
		//get event informations if member of group
		global $db;
		
		$data = $db->doQueryWithArgs("SELECT group_id FROM group_relations WHERE member_id=? AND member_type=3", array($_GET['id']), "i");
		if(count($data)==1){
			
			$group_id = $data[0]["group_id"];
			
			$options = $db->doQueryWithArgs("SELECT event_type_options.option_key,event_options.value FROM event_type_options LEFT JOIN event_options ON event_type_options.id=event_options.event_type_option_id WHERE event_type_options.id=? AND event_options.event_id=?", array($event_type_id, $_GET['id']), "ii");
			
			$JSON['event']=$options;
			
		}else{
			addError(getMessages()->UNKNOWN_ERROR(7));
		}
	}
}else{
	if($is_post){
		//create new event
		
		if(isset($_POST['event_title']) && isset($_POST['event_type_id']) && isset($_POST['parent_group_id']) && isset($_POST['options'])){
			if(currentUserCan("add_members", $_POST['parent_group_id'])){
				if(is_array($_POST['options'])){
					global $db;
					$data = $db->doQueryWithArgs("SELECT id, option_key, input_data_type FROM event_type_options WHERE required = 1 AND event_type_id=?", array($_POST['event_type_id']), "i");
					
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
						
						//get next auto-increament id
						
						$data = $db->doQueryWithoutArgs("SHOW TABLE STATUS LIKE 'events'");
						$id = $data[0]['Auto_increment'];
						
						
						$db->doQueryWithArgs("INSERT INTO events(id, title,type_id) VALUES(?,?,?)", array($id, $_POST['event_title'], $_POST['event_type_id']), "isi");
						
						$qm=array();
						$values=array();
						$types="";
						foreach($options as $event_type_option_id => $option){
							$qm[]="(?,?,?)";
							$values[]=$id;
							$values[]=$event_type_option_id;
							$values[]=$option;
							$types.="iis";
						}
						
						$db->doQueryWithArgs("INSERT INTO event_options(event_id,event_type_option_id,value) VALUES ".implode(", ", $qm), $values, $types);
						
						//set parent group
						
						$db->doQueryWithArgs("INSERT INTO group_relations(member_id,group_id,member_type) VALUES(?,?,3)", array($id,$_POST['parent_group_id']), "ii");
						
					}else{
						addError(getMessages()->ERROR_API_REQUIRED_FIELDS);
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
		
	}else if($is_delete){
		if(isset($_DELETE['event_id']){
			//delete event
			
			//get parent group id
			
			global $db;
			$data = $db->doQueryWithArgs("SELECT group_id FROM group_relations WHERE member_id=? AND member_type=3", array($_DELETE['event_id']), "i");
			if(count($data)==1){
				if(currentUserCan("manage_options", $data[0]["group_id"])){
					deleteEvent($_DELETE['event_id']);
				}else{
					addError(getMessages()->ERROR_API_PRIVILEGES);
				}
			}else{
				addError(getMessages()->ERROR_API_INVALID_INPUT);
			}
		}
	}else if($is_get){
		//list all events [filters]
		
		global $db;
		
		$filters=getFilters();
		
		if($filters!=false){
			
			$query="SELECT groups.id,groups.name FROM groups LEFT JOIN group_types ON groups.type_id=group_types.id WHERE 1=1";
		
			$groups=array();
			$args=array();
			$types="";
			
			if(isset($filters['group_parent_id'])){
					
				$query="SELECT events.id,events.title FROM (".
					"SELECT * from group_relations LEFT JOIN events ON group_relations.member_id=events.id WHERE group_relations.group_id=? AND member_type=3".
				") LEFT JOIN event_types ON events.type_id=event_types.id WHERE 1=1";
				$args[]=$filters['group_parent_id'];
				$type.="i";
				
			}
			
			if(isset($filters['title'])){
				$query.=" AND events.title LIKE ?";
				$args[]="%".$filters['title']."%";
				$types.="s";
			}
			if(isset($filters['event_type_id'])){
				$query.=" AND events.type_id = ?";
				$args[]=$filters['event_type_id'];
				$types.="s";
			}
			
			if(isset($filters['items_per_page']) && isset($filters['page'])){
				$query.=" LIMIT ?,?";
				$args[]=$filters['items_per_page'];
				$args[]=(((int)$filters['items_per_page']) * ((int)$filters['page']));
				$types.="ii";
			}
			
			if(!empty($args)){
				$events=$db->doQueryWithArgs($query, $args, $types);
			}else{
				addError(getMessages()->ERROR_API_EVENTS_LIST_ALL);
			}
			
			$JSON["events"]=$events;
			
		}else{
			addError(getMessages()->ERROR_API_EVENTS_LIST_ALL);
		}
		
	}else{
		addError(getMessages()->ERROR_API_REQUIRED_FIELDS);
	}
}

function getEventOptions($event_type_id){
	$event_options=$db->doQueryWithArgs("SELECT * FROM event_type_options WHERE event_type_id=?", array($group_type_id), "i");
	$event_options[]=array(
		'key'=>'title'
		'input_data_type'=>'text',
		'options'=>'{"input_type":"textfield"}',
		'description'=>getMessages()->GROUP_OPTIONS_TITLE_DESC;
	);
	return $event_options;
}
	
?>