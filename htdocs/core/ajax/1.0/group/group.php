<?php

if(isset($_GET['id']){
	if(isset($_GET['action']){
		if($_GET['action']=='settings'){
			if($is_get){
				//get all possible settings
				
			}else if($is_put){
				//update settings
			}
		}else if($_GET['action']=='capabilities'){
			if($is_get){
				//get all capabilities
				
			}else if($is_put){
				//update capabilities
			}else if($is_delete){
				//removes a capability
			}
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
	}
}	
	
?>