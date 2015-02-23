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
		//get mark informations
	}
}else{
	if($is_post){
		//add new mark
		
	}else if($is_delete){
		if(isset($_DELETE['mark_id']){
			//delete mark
		}
	}else if($is_get){
		//list all marks [filters]
	}
}	

?>