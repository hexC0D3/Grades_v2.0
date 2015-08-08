<?php

if(isset($_GET['id'])){
	if(isset($_GET['action'])){
		if($_GET['action']=='settings'){
			if($is_get){
				//get all possible settings
				
				$JSON["settings"]=array();
				$JSON["settings"][]=array(
					'key'=>'grade',
					'input_data_type'=>'float',
					'options'=>'{"input_type":"grade"}',
					'description'=>getMessages()->GRADE_OPTIONS_GRADE_DESC
				);
					
				$JSON["settings"][]=array(
					'key'=>'test_id',
					'input_data_type'=>'event_id:test',
					'options'=>'{"input_type":"event:test"}',
					'description'=>getMessages()->GRADE_OPTIONS_GRADE_TEST_DESC
				);
				
			}else if($is_put){
				//update settings
				
				global $db;
				
				$global_options=array(
					'grade' => 'float'
				);
				
				if(isset($_PUT['option_key']) && isset($_PUT['value'])){
					
					if(in_array($_PUT['option_key'], array_keys($global_options))){
						$validation = validateDynamicInput($_PUT['value'], $global_options[$_PUT['option_key']]);
						$_PUT['value']=$validation[1];
						
						if($validation[0]){
							$db->doQueryWithArgs("UPDATE grades SET ".$_PUT['option_key']."=? WHERE id=?", array($_PUT['value'],$_GET['id']), "si");
						}else{
							addError(getMessages()->ERROR_API_INVALID_INPUT);
						}
					}else{
						addError(getMessages()->ERROR_API_INVALID_INPUT);
					}
					
				}else{
					addError(getMessages()->ERROR_API_INVALID_INPUT);
				}
			}
		}
	}else{
		//get grade informations
		global $db;
		
		$data = $db->doQueryWithArgs("SELECT id,event_id,grade FROM grades WHERE id=? AND user_id=?", array($_GET['id'], getUser()['id']), "i");
		if(count($data) == 0){
			$JSON['grade']=$data[0];
		}else{
			addError(getMessages()->ERROR_API_PRIVILEGES);
		}
	}
}else{
	if($is_post){
		//add new grade
		
		if(isset($_POST['grade']) && isset($_POST['event_id'])){
			
			$v1 = validateDynamicInput($_POST['grade'], "float");
			$_POST['grade'] = $v1[1];
			
			$v2 = validateDynamicInput($_POST['event_id'], "event_id:test");
			$_POST['event_id'] = $v2[1];
			
			if($v1[0] && $v2[0]){
				
				global $db;
				
				//get event group and check if user is member of it
				
				$data = $db->doQueryWithArgs("SELECT group_id FROM group_relations WHERE member_id=? AND member_type=3", array($_POST['event_id']), "i");
				
				if(count($data) == 1){
					if(isInGroup($data[0]["group_id"], 1, getUser()['id'])){
						
						//check if grade already exists
						
						$check = $db->doQueryWithArgs("SELECT * FROM grades WHERE user_id=? AND event_id=?", array(getUser()['id'],$_POST['event_id']), "ii");
						
						if(count($check) == 0){
							
							$db->doQueryWithArgs("INSERT INTO grades(user_id,event_id,grade) VALUES(?,?,?)", array(getUser()['id'],$_POST['event_id'],$_POST['grade']), "iid");
						}else{
							addError(getMessages()->ERROR_GRADES_ALREADY_EXISTS);
						}
						
					}else{
						addError(getMessages()->ERROR_API_PRIVILEGES);
					}
				}else{
					addError(getMessages()->UNKNOWN_ERROR(11));
				}
			}
		}
		
	}else if($is_delete){
		if(isset($_DELETE['grade_id'])){
			//delete grade
			$data = $db->doQueryWithArgs("DELETE FROM grades WHERE id=? AND user_id=?", array($_DELETE['grade_id'], getUser()['id']), "ii");
		}
	}else if($is_get){
		//list all grades [filters]
		
		$filters=getFilters();
		
		$query="SELECT g.*,group_options.value as subject FROM (SELECT grades.grade as grade, events.id as test_id, groups.id as group_id, SUBSTRING_INDEX(GROUP_CONCAT(event_options.value ORDER BY event_type_options.option_key DESC), ',', 1) as lesson_id,SUBSTRING_INDEX(GROUP_CONCAT(event_options.value ORDER BY event_type_options.option_key ASC), ',', 1) as grade_weight FROM grades LEFT JOIN events ON grades.event_id=events.id LEFT JOIN event_options ON events.id=event_options.event_id LEFT JOIN event_type_options ON event_options.event_type_option_id=event_type_options.id LEFT JOIN group_relations ON events.id=group_relations.member_id LEFT JOIN groups ON group_relations.group_id=groups.id WHERE grades.user_id=? AND group_relations.member_type=3  AND (event_type_options.option_key='lesson_id' OR event_type_options.option_key='grade_weight')) as g LEFT JOIN group_relations ON g.lesson_id = group_relations.member_id LEFT JOIN groups ON group_relations.group_id=groups.id LEFT JOIN group_type_options ON groups.type_id=group_type_options.group_type_id LEFT JOIN group_options ON group_type_options.id=group_options.group_type_option_id WHERE group_relations.member_type=3 AND group_type_options.option_key='subject'";
		$values=array(getUser()['id']);
		$types="i";
		
		if($filters!=false){
			
			if(isset($filters['test_id'])){
				$query.=" AND g.test_id = ?";
				$args[]=$filters['test_id'];
				$types.="i";
			}
			if(isset($filters['group_id'])){
				$query.=" AND g.group_id = ?";
				$args[]=$filters['group_id'];
				$types.="i";
			}
			if(isset($filters['subject'])){
				$query.=" AND subject LIKE %?%";
				$args[]=$filters['subject'];
				$types.="s";
			}
			if(isset($filters['grade_weight'])){
				$query.=" AND g.grade_weight = ?";
				$args[]=$filters['grade_weight'];
				$types.="s";/* Transformed to string by group by and substring_index */
			}
			if(isset($filters['grade'])){
				$query.=" AND g.grade = ?";
				$args[]=$filters['grade'];
				$types.="d";
			}
			
		}
		
		$grades = $db->doQueryWithArgs($query, $values, $types);
		
		$groups = array();
		$avgs = array();
		
		// group by group id
		
		foreach($grades as $grade){
			
			$groups['group_id'][] = $grade;
			
		}
		
		foreach($groups as $group_id => $grades){
			$sum = 0;
			$weight = 0;
			
			foreach($grades as $grade){
				$sum+=($grade['grade'] * $grade['grade_weight']);
				$weight+=$grade['grade_weight'];
			}
			
			$avgs[] = ($sum/$weight);
			
		}
		
		$points = 0;
		$sum = 0;
		
		$mcm = getUser()['mark_calc_method'];
		
		if($mcm == 0){ /*If is outside and loop is inside because it's more efficent!*/
				
			foreach($avgs as $avg){
				$sum+=$avg;
			}
			
		}else if($mcm == 1){
			
			foreach($avgs as $avg){
				$sum+=$avg;
				
				$p=($avg - 4);
				
				$points+=$p;
			}
			
		}else if($mcm == 2){
			
			foreach($avgs as $avg){
				$sum+=$avg;
				
				$p=($avg - 4);
				if($p<0){$p*=2;}
				
				$points+=$p;
			}
			
		}
		
		$JSON['points'] = $points;
		$JSON['average'] = ($sum/(count($avgs)));
		
		$JSON['grades'] = $grades;
		
	}
}	

?>