<?php
	
if(isset($_GET['id']){
	if(isset($_GET['action']){
		if($_GET['action']=='settings'){
			if($is_get){
				//get all possible settings
				
			}else if($is_put){
				//update settings
			}
		}
	}else{
		//get event informations
	}
}else{
	if($is_post){
		//create new event
		
	}else if($is_delete){
		if(isset($_DELETE['event_id']){
			//delete event
		}
	}else if($is_get){
		//list all events [filters]
	}
}	
	
?>