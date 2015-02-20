<?php
	
/*
	* Define needed functions
*/

/** Checks if the current user is logged in **/
function isUserLoggedIn(){
	if(is_set($_SESSION['user'])){
		return true;
	}
	return false;
}

/** Returns the current user data **/
function getUser(){
	global $user;
	if(!empty($user)){
		return $user;
	}
	return $user=$_SESSION['user'];
}

/** Logs in a user **/
function logInUser($mail, $password){
	global $db;
	$password=password_hash($password, PASSWORD_HASH);
	
	$user=$db->doQueryWithArgs("SELECT * from users WHERE mail=? AND password=?", array($mail, $password), "ss");
	if(!empty($user)&&$user!=false&&count($user)==1){
		$_SESSION['user']=$user[0];
		return true;
	}
	addError(getMessages()->ERROR_LOGIN_UNKNOWN);
	
	return false;
}

/** Checks if a user already exists or not **/
function isMailRegistered($mail){
	if(filter_var($mail, FILTER_VALIDATE_EMAIL)){
		global $db;
		$data=$db->doQueryWithArgs("SELECT COUNT(mail) as mailCount FROM users WHERE mail=?", array($mail), "s");
		if(count($data)==1){
			if(isset($data[0]['mailCount'])&&$data[0]['mailCount']==0){
				return false;
			}else{
				addError(getMessages()->ERROR_REGISTER_MAIL_ALREADY_IN_USE);
			}
		}else{
			addError(getMessages()->ERROR_REGISTER_UNKNOWN);
		}
	}else{
		addError(getMessages()->ERROR_MAIL_INVALID);
	}
	return true;
}

/** Registers a user **/
function registerUser($mail){
	if(!isMailRegistered($mail)){
		//start with registering itself
		global $db;
		
		if($db->doQueryWithArgs("INSERT INTO users(mail, password) VALUES(?, 'registering')", array($mail), "s")==true){
			
			$token=addPasswordResetToken($mail);
			
			if($token!=false){
				
				sendMail($mail, SUPPORT_MAIL, getMessages()->MAIL_SUPPORT_NAME, getMessages()->MAIL_REGISTER_SUBJECT,
				getMessages()->MAIL_REGISTER_BODY_PART.
				" <a href='".DOMAIN."/?".http_build_query(array('token'=>$token, 'mail'=>$mail)).">".
				getMessages()->MAIL_REGISTER_BODY_LINK."</a><br/>".getMessages()->MAIL_REGARDS);
				
			}else{
				addError(getMessages()->ERROR_RESET_PW_UNKNOWN);
			}

			
			return true;
		}else{
			addError(getMessages()->ERROR_GEN_TOKEN);
		}
	}
	return false;
}

/** Check if a user is in the process of registering **/
function isUserRegistering($mail){
	global $db;
	
	$data=$db->doQueryWithArgs("SELECT password FROM users WHERE mail=?", array($mail), "s");
	if(count($data)==1&&$data[0]['password']=="registering"){
		return true;
	}
	
	return false;
}
/** Resets the password of a user **/
function resetPassword($mail, $token, $password){
	if(validatePasswordResetToken($token, $mail)){
		
		$password=password_hash($password, PASSWORD_HASH);
		
		global $db;
		if($db->doQueryWithArgs("UPDATE users SET password WHERE mail=?", array($password, $mail), "ss")==true){
			return true;
		}else{
			return false;
		}
		
		$db->doQueryWithArgs("DELETE FROM reset_tokens WHERE mail=?", array($mail), array(s));
	}
}

/** Valdiate password reset token **/
function validatePasswordResetToken($token, $mail){
	global $db;
	
	$curTime=date('Y-m-d H:i:s');//current time
	
	clearResetTokens($curTime);
	
	$token=$db->doQueryWithArgs("SELECT id FROM reset_tokens WHERE mail=? AND token=? AND ip=? AND timestamp >= DATE_SUB(?, INTERVAL 2 HOUR)", array($mail, $token, $_SERVER['REMOTE_ADDR'], $curTime), "ssss");
	if(count($token)==1&&isset($token[0]['id'])){
		return true;
	}
	return false;
}

/** Adds a password reset token **/
function addPasswordResetToken($mail){
	global $db;
	
	$token=md5(uniqid(mt_rand(), true)); //random token
	$curTime=date('Y-m-d H:i:s');//current time
	$ip=$_SERVER['REMOTE_ADDR'];//ip of current user
	
	clearResetTokens($curTime);
	
	if($db->doQueryWithArgs("INSERT INTO reset_tokens(mail, token, ip, timestamp) VALUES(?, ?, ?, ?)", array($mail, $token, $ip, $curTime), 'ssss')==true){
		return $token;
	}
	
	return false;
}

/**Removes old reset tokens **/
function clearResetTokens($curTime){
	global $db;
	$db->doQueryWithArgs("DELETE FROM reset_tokens WHERE timestamp <= DATE_SUB(?, INTERVAL 2 HOUR)", array($curTime), "s");
}

/** Request a reset of a user password **/
function requestPasswordReset($mail){
	$token=addPasswordResetToken($mail);
	if($token!=false){
		
		sendMail($mail, SUPPORT_MAIL, getMessages()->MAIL_SUPPORT_NAME, getMessages()->MAIL_PASSWORD_RESET_SUBJECT,
		getMessages()->MAIL_PASSWORD_RESET_BODY_PART.
		" <a href='".DOMAIN."/?".http_build_query(array('token'=>$token, 'mail'=>$mail)).">".
		getMessages()->MAIL_PASSWORD_RESET_BODY_LINK."</a><br/>".getMessages()->MAIL_REGARDS);
		
	}else{
		addError(getMessages()->ERROR_GEN_TOKEN);
	}
	return false;
}
	
?>