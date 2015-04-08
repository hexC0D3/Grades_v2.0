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
							
	if($value=='number'){
		$valid=is_numeric($value);
	}else if($value=='text'){
		$valid=is_string($value);
		$value=htmlentities(stripslashes(strip_tags($value)));
	}else if($value=='boolean'){
		$value=(bool)$value;
	}else if($value=='timestamp'){
		$valid=((string) (int) $timestamp === $timestamp) && ($timestamp <= PHP_INT_MAX) && ($timestamp > 0);
	}
	
	return $valid;
}
	
?>