<?php
	
if(!defined('grades_loaded')){
	//header("Location: /");
	die("INSECURE LOAD!");
}
	
/*
	* Starting with the grades core file by defining our absolute paths
*/

define("AJAX_DIR", ROOT_DIR  .'ajax/');
define("CSS_DIR" , ROOT_DIR  .'css/');
define("FONT_DIR", ROOT_DIR  .'fonts/');
define("IMG_DIR" , ROOT_DIR  .'img/');

/*
	* Now we include the database
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
	* If not started yet, we start a php session by setting a session cookie
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
	* Now we load out auth systems
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
		$value = htmlentities(stripslashes(strip_tags($value)));
	}else if($type == 'boolean'){
		$value = (bool)$value;
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
	//foreach test foreach mark
	
	$db->doQueryWithArgs("DELETE FROM subjects WHERE id=?",array($subject_id), "i");
}

function isUserMemberOf($group_id){
	global $db;
	
	//get direct parent group
	
	$data = $db->doQueryWithArgs("SELECT group_id FROM group_relations WHERE member_id=? AND member_type=1", array(getUser()['id']), "i");
	
	if(count($data) == 1){
		$db->doQueryWithArgs("CALL `getParentGroups`(?, @p1);", array($data[0]["group_id"]), "i");
		$data = $db->doQueryWithoutArgs("SELECT @p1 AS group_ids;");
		if(count($data)==1){
			$ids = explode(",", $data[0]['group_ids']);
			return in_array($group_id, $ids);
		}else{
			addError(getMessages()->UNKNOWN_ERROR(9));
		}

	}else{
		addError(getMessages()->UNKNOWN_ERROR(10));
	}
	
	return false;
}
	
?>