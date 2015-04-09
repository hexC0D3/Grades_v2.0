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
	
	$ids = array('user_id' => 'users', 'subject_id' => 'subjects', 'group_id' => 'groups');
							
	if($type == 'number'){
		$valid = is_numeric($value);
		$value = (int)$value;
	}else if($type=='text'){
		$valid = is_string($value);
		$value = htmlentities(stripslashes(strip_tags($value)));
	}else if($type == 'boolean'){
		$value = (bool)$value;
	}else if($type == 'timestamp'){
		$valid = ((string) (int) $value === $value) && ($value <= PHP_INT_MAX) && ($value > 0);
	}else if($type == 'mark_calc_method' && is_numeric($value)){
		
		$value=(int)$value;
		
		$valid=in_array($value, array(0,1);

		/*
			* 0 : Swiss grade calculation, 1 => worst, 6 => best
			* 1 : German grade calculation, 6 => worst, 1 => best
			...	
		*/

	}else if(in_array($type, array_keys($ids)) && is_numeric($value)){
		$value = (int)$value;
		$table = $ids[$type];
		
		global $db;
		
		$data = $db->doQueryWithArgs("SELECT id FROM ".$table." WHERE id=?", array($value), "i");
		
		if(count($data) == 1){ /* Check if given id exists in table */
			$valid = true;
		}else{
			addError(getMessages()->ERROR_API_INVALID_INPUT);
		}
		
	}
	
	return array($valid, $value);
}
	
?>