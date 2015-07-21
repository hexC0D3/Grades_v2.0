<?php
	
if(!defined('grades_loaded')){
	header("Location: /");
}

/*
	* We include the database access file
*/

require_once 'db.php';

/*
	* Define some important stuff
*/

date_default_timezone_set('Europe/Zurich');

define("PASSWORD_HASH", PASSWORD_DEFAULT);

define("DOMAIN", "grades.hexcode.ch");
define("SUPPORT_MAIL", "support@grades.hexcode.ch");

/** global Error object **/
class Errors{
	private $errorList;
	
	public function Errors($errorList=array()){
		$this->errorList=$errorList;
	}
	public function addError($error=""){
		$this->errorList[]=$error;
	}
	public function getErrors(){
		return $this->errorList;
	}
}

global $errors;
$errors=new Errors();

function addError($error){
	global $errors;
	
	$errors->addError($error);
}
function getErrors(){
	global $errors;
	
	return $errors->getErrors();
}

/*
	* If not started yet, we start a php session
*/

if(session_status() != PHP_SESSION_ACTIVE) {
	session_start();
}

/*
	* Start localization by including the language script
*/

require_once 'lang.php';

/*
	* Load message files
*/

require_once CORE_DIR."/messages.php";

/*
	* Now we load our auth system
*/

require_once 'auth.php';

/*
	* Functions
*/

/** Gets current page url **/
function curPageURL(){
	$pageURL = 'https://'.$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
	return $pageURL;
}

/** Sends a mail **/
function sendMail($toMail, $fromMail, $fromName, $subject, $message){
	$header  = 'MIME-Version: 1.0' . "\r\n";
	$header .= 'Content-type: text/html; charset=utf-8' . "\r\n";
	$header .= 'From: '.$fromName.' <'.$fromMail.'>' . "\r\n";
	return mail($toMail, $subject, $message, $header);
}

/** Requires all files in a directory **/
function require_all($pathToFolder){
	
	foreach (scandir($pathToFolder) as $filename){
		$path=$pathToFolder.$filename;
		
		if(is_file($path)){
			require_once $path;
		}
	}
}

/** Validates dynamic option update values **/
function validateDynamicInput($value, $type){
	
	$valid=false;
	
	$ids = array('user_id' => 'users', 'subject_id' => 'subjects', 'group_id' => 'groups', 'event_id' => 'events');
							
	if($type == 'int'){
		$valid = is_numeric($value);
		$value = (int)$value + 0;
	}else if($type=='float'){
		$valid = is_numeric($value);
		$value = (float)$value + 0;
	}else if($type=='text'){
		$valid = is_string($value);
		$value = stripslashes(strip_tags($value));
	}else if($type=='url'){
		$valid = (!filter_var($value, FILTER_VALIDATE_URL) === false);
	}else if($type == 'boolean'){
		$value = (int)($value === 'true');
		$valid = true;
	}else if($type == 'timestamp'){
		$valid = ((string) (int) $value === $value) && ($value <= PHP_INT_MAX) && ($value > 0);
	}else if($type == 'mark_calc_method' && is_numeric($value)){
		
		$value=(int)$value;
		
		$valid=in_array($value, array(0,1,2));

		/*
			* 0 : switzerland, 1 => worst, 6 => best, 4 => ok
			* 1 : switzerland, 1 => worst, 6 => best, 4 => ok, Saldopunkte 1x negativ
			* 2 : switzerland, 1 => worst, 6 => best, 4 => ok, Saldopunkte 2x negativ
			...	
		*/

	}else{		
		global $db;
		
		$values=array();
		$types="";
		
		$type_t=-1;
		$AND = "";
		
		if (strpos($type,':') !== false) {
			$ex = explode(":", $type);
			$type = $ex[0];
			$type_t = $ex[1];
			
			$data = null;
			
			if($type == "event_id"){
				$data = $db->doQueryWithArgs("SELECT id FROM event_types WHERE title=?", array($type_t), "s");
			}else if($type == "group_id"){
				$data = $db->doQueryWithArgs("SELECT id FROM group_types WHERE title=?", array($type_t), "s");
			}
			
			if($data != null){
				if(count($data) == 1){
					$id = $data[0]["id"];
					
					$AND = " AND type_id = ?";
					$values[]=$id;
					$types.="i";
					
				}else{
					addError(getMessages()->UNKNOWN_ERROR(8));
				}
			}
		}
		
		if(in_array($type, array_keys($ids)) && is_numeric($value)){
			
			$value = (int)$value;
			$table = $ids[$type];
			
			$values[]=$value;
			$types.="i";
			
			$data = $db->doQueryWithArgs("SELECT id FROM ".$table." WHERE id=?".$AND, $values, $types);
			
			if(count($data) == 1){ /* Check if given id exists in table */
				$valid = true;
			}else{
				addError(getMessages()->ERROR_API_INVALID_INPUT);
			}
		}
	}
	
	return array($valid, $value);
}

function translateOptions($options){
	$data = json_decode($options);
	
	if($data->input_type == 'select'){
		foreach($data->select as $key => $option){
			
			$trans_key = $option->title_tanslation_key;
			
			unset($data->select[$key]->title_tanslation_key);
			$data->select[$key]->description = getMessages()->$trans_key;
		}
	}
	
	return $data;
}

/** Various delete functions **/
function deleteGroup($group_id){
	global $db;
	//get all sub groups
	
	$members = $db->doQueryWithArgs("SELECT * FROM group_relations WHERE group_id=?", array($group_id), "i");
	foreach($members as $member){
		if($member['member_type'] == 1){
			//only delete relation
		}else if($member['member_type'] == 2){
			deleteGroup($member['member_id']);
			
		}else if($member['member_type'] == 3){
			deleteEvent($member['member_id']);
			
		}else if($member['member_type'] == 4){
			deleteSubject($member['member_id']);
		}
		
		$db->doQueryWithArgs("DELETE FROM group_relations WHERE member_id=? AND member_type=?",array($member['id'],$member['member_type']), "ii");
	}
	
	$db->doQueryWithArgs("DELETE FROM group_options WHERE group_id=?",array($group_id), "i");
	$db->doQueryWithArgs("DELETE FROM groups WHERE id=?",array($group_id), "i");
}

function deleteEvent($event_id){
	global $db;
	$db->doQueryWithArgs("DELETE FROM event_options WHERE event_id=?",array($event_id), "i");
	$db->doQueryWithArgs("DELETE FROM events WHERE id=?",array($event_id), "i");
}

function deleteSubject($subject_id){
	global $db;
	//foreach test foreach grade
	
	$events = $db->doQueryWithArgs("SELECT event_options.event_id as id FROM event_options LEFT JOIN event_type_options ON event_options.event_type_option_id = event_type_options.id WHERE event_type_options.option_key = 'subject_id' AND event_options.value=?", array($subject_id), "i");
	
	if(count($events) > 0){
		
		$conditions = "WHERE event_id=?";
		$values = array($events[0]['id']);
		$types = "i";
		
		for($i=1;$i<count($events);$i++){
			$conditions .= " OR event_id=?";
			$values[] = $events[$i]['id'];
			$types .= "i";
		}
		
		$db->doQueryWithArgs("DELETE FROM grades".$conditions, $values, $types);
	}
	
	
	$db->doQueryWithArgs("DELETE FROM subjects WHERE id=?",array($subject_id), "i");
}

function deleteGrades($user_id, $group_id){
	
	global $db;
	
	$events = $db->doQueryWithArgs("SELECT group_relations.member_id as id FROM group_relations WHERE group_relations.member_type=3 AND group_id=?", array($group_id), "i");
	
	if(count($events) > 0){
		
		$conditions = "WHERE event_id=?";
		$values = array($events[0]['id']);
		$types = "i";
		
		for($i=1;$i<count($events);$i++){
			$conditions .= " OR event_id=?";
			$values[] = $events[$i]['id'];
			$types .= "i";
		}
		
		$db->doQueryWithArgs("DELETE FROM grades".$conditions, $values, $types);
	}
	
}

function getParentGroups($member_type_id, $member_id){
	
	global $db;
	
	$data = $db->doQueryWithArgs("SELECT group_id FROM group_relations WHERE member_id=? AND member_type=?", array($member_id, $member_type_id), "ii");
	
	$g_ids = array();
	
	foreach($data as $data_set){
	
		$db->doQueryWithArgs("CALL `getParentGroups`(?, @p2);", array($data_set["group_id"]), "i");
		$group_member_ids = $db->doQueryWithoutArgs("SELECT @p2 AS group_member_ids");
		
		if(count($group_member_ids) == 1){
			
			$group_member_ids = explode(",", $group_member_ids[0]['group_member_ids']);
			$g_ids = array_merge($g_ids, $group_member_ids);
			
		}else{
			addError(getMessages()->UNKNOWN_ERROR(14));
		}
		
	}
	
	return array_unique($g_ids);
	
}

function isInGroup($group_id, $member_type_id, $member_id){
	global $db;
	
	//get direct parent group
	
	$data = $db->doQueryWithArgs("SELECT group_id FROM group_relations WHERE member_id=? AND member_type=?", array($member_id, $member_type_id), "ii");
	
	$result = false;
	
	foreach($data as $data_set){
		
		$db->doQueryWithArgs("CALL `getParentGroups`(?, @p2);", array($data_set["group_id"]), "i");
		$group_member_ids = $db->doQueryWithoutArgs("SELECT @p2 AS group_member_ids");
		
		if(count($group_member_ids) == 1){
			
			$group_member_ids = explode(",", $group_member_ids[0]['group_member_ids']);
			$result = $result || (in_array($group_id, $group_member_ids));
			
		}else{
			addError(getMessages()->UNKNOWN_ERROR(9));
		}
		
	}
	
	return $result;
}

/*function isInGroup($group_id, $member_type_id, $member_id){
	global $db;
	
	//get direct parent group
	
	$data = $db->doQueryWithArgs("SELECT group_id FROM group_relations WHERE member_id=? AND member_type=?", array($member_id, $member_type_id), "ii");
	
	$result = true;
	
	$db->doQueryWithArgs("CALL `getParentGroups`(?, @p1);", array($group_id), "i");
	$group_ids = $db->doQueryWithoutArgs("SELECT @p1 AS group_ids;");
	if(count($group_ids) == 1){
	
		$group_ids = explode(",", $group_ids[0]['group_ids']);
		
		foreach($data as $data_set){
		
			$db->doQueryWithArgs("CALL `getParentGroups`(?, @p2);", array($data_set["group_id"]), "i");
			$group_member_ids = $db->doQueryWithoutArgs("SELECT @p2 AS group_member_ids");
			
			if(count($group_member_ids) == 1){
				
				$group_member_ids = explode(",", $group_member_ids[0]['group_member_ids']);
				$result = $result && (count(array_intersect($group_member_ids, $group_ids)) > 0);
				
			}else{
				addError(getMessages()->UNKNOWN_ERROR(9));
			}
			
		}
		
	}else{
		addError(getMessages()->UNKNOWN_ERROR(13));
	}
	
	return $result;
}*/

function currentUserCanJoin($group_id){
	global $db;
	
	$data = $db->doQueryWithArgs("SELECT COUNT(*) as count FROM group_relations WHERE member_id=? AND group_id=? AND member_type=1", array(getUser()['id'], $group_id), "ii");
	
	if(count($data) == 1){
		return ($data[0]['count'] == 0);
	}
	
	return false;
}

function currentUserCanLeave($group_id){
	
	global $db;
	
	if(currentUserCan('manage_capabilities', $group_id)){
		
		$count = $db->doQueryWithArgs("SELECT COUNT(*) as count FROM group_capabilities LEFT JOIN group_relations ON group_capabilities.relation_id = group_relations.id WHERE capability='manage_capabilities' AND group_relations.group_id=?", array($group_id), "i");
		
		if($count[0]['count'] <= 1){
			return false;
		}
		
	}
	
	return !currentUserCanJoin($group_id);
}

function getCurrentUserCapabilities($group_id){
	global $db;
	
	$caps = $db->doQueryWithArgs("SELECT caps FROM v_user_caps WHERE user_id=? AND group_id=?", array(getUser()["id"], $group_id), "ii");
	
	if(count($caps) == 1){
		return explode(",", $caps[0]["caps"]);
	};
	
	return array();
}
	
?>