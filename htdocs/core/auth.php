<?php
	
/*
	* Define needed functions
*/

/** Checks if the current user is logged in **/
function isUserLoggedIn(){
	return !is_null(getUser());
}

/** Returns the current user data **/
function getUser(){
	global $user, $db;
	if(!empty($user)){
		return $user;
	}
	if($SESSION_TOKEN){
		return $user=$db->doQueryWithArgs("SELECT users.id, users.mail, user_meta.* FROM users LEFT JOIN user_meta ON users.id=user_meta.user_id LEFT JOIN login_tokens ON users.id=login_tokens.user_id WHERE login_tokens.login_token=? ", array($SESSION_TOKEN), "s")[0];
	}else{
		return null;
	}
}

/** Logs in a user **/
function logInUser($mail, $password){
	global $db;
	$password=password_hash($password, PASSWORD_HASH);
	
	$user=$db->doQueryWithArgs("SELECT * from users WHERE mail=? AND password=?", array($mail, $password), "ss");
	if(!empty($user)&&$user!=false&&count($user)==1){
		$user=$user[0];
		
		//generate unique login token
		$token=bin2hex(openssl_random_pseudo_bytes(16));
		while($db->doQueryWithArgs("SELECT COUNT(*) as count FROM login_tokens WHERE login_token=?", array($token), "s")[0]["count"]>0){
			$token=bin2hex(openssl_random_pseudo_bytes(16));
		}
		$db->doQueryWithArgs("INSERT INTO login_tokens(user_id, login_token, ip) VALUES(?, ?, ?)", array($user["id"], $token, $_SERVER['REMOTE_ADDR']), "iss");
		
		$JSON['session_token']=$token:
		
		return true;
	}
	addError(getMessages()->ERROR_LOGIN_UNKNOWN);
	
	return false;
}

/** Checks if a user already exists or not **/
function isMailRegistered($mail){
	if(filter_var($mail, FILTER_VALIDATE_EMAIL)){
		global $db;
		$data=$db->doQueryWithArgs("SELECT * FROM users WHERE mail=?", array($mail), "s");
		if(count($data)==0){
			//everything is fine, mail not registered
			return false;
		}else if(count($data)==1){
			//mail already registered
			if($data[0]['password'] == 'registering'){
				//user isn't verified by mail yet
				addError(getMessages()->ERROR_REGISTER_MAIL_ALREADY_IN_USE_NOT_VERIFIED);
			}else{
				//user is verified
				addError(getMessages()->ERROR_REGISTER_MAIL_ALREADY_IN_USE_VERIFIED);
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
function registerUser($mail, $captach_val){
	if(!isMailRegistered($mail)){
		//start with registering itself
		global $db;
		
		$ch=curl_init();
		curl_setopt($ch, CURLOPT_URL, 'https://www.google.com/recaptcha/api/siteverify');
		curl_setopt($ch,CURLOPT_POST, 3);
		curl_setopt($ch,CURLOPT_POSTFIELDS, 'secret=6Le-hQITAAAAAP-AiMlRkPXUGW8vx4fb6oXWhZre&response='.$captach_val."&remoteip=".$_SERVER['REMOTE_ADDR']);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$json_=curl_exec($ch);
		$obj=json_decode($json_);
		
		if($obj->success==true){
			if($db->doQueryWithArgs("INSERT INTO users(mail, password) VALUES(?, 'registering')", array($mail), "s")==true){
			
				$token=addPasswordResetToken($mail);
				
				if($token!=false){
					
					sendMail($mail, SUPPORT_MAIL, getMessages()->MAIL_SUPPORT_NAME, getMessages()->MAIL_REGISTER_SUBJECT,
					getMessages()->MAIL_REGISTER_BODY_PART." ".$token.
					"<br>".getMessages()->MAIL_REGARDS);
					
				}else{
					addError(getMessages()->ERROR_RESET_PW_UNKNOWN);
				}
	
				
				return true;
			}else{
				addError(getMessages()->ERROR_GEN_TOKEN);
			}
		}else{
			addError(getMessages()->ERROR_REGISTER_INVALID_CAPTCHA);
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
		
		if(strlen($password)>5){
			$password=password_hash($password, PASSWORD_HASH);
		
			global $db;
			if($db->doQueryWithArgs("UPDATE users SET password WHERE mail=?", array($password, $mail), "ss")==true){
				return true;
			}else{
				return false;
			}
			
			$db->doQueryWithArgs("DELETE FROM reset_tokens WHERE mail=?", array($mail), array(s));
		}else{
			addError(getMessages()->ERROR_SET_PASSWORD_STRENGTH_LENGTH);
		}
	}else{
		addError(getMessages()->ERROR_SET_PASSWORD_INVALID_CODE);
	}
}

/** Valdiate password reset token **/
function validatePasswordResetToken($token, $mail){
	global $db;
	
	clearResetTokens();
	clearUnverifiedUsers();
	
	$token=$db->doQueryWithArgs("SELECT id FROM reset_tokens WHERE mail=? AND token=? AND ip=? ", array($mail, $token, $_SERVER['REMOTE_ADDR']), "sss");
	if(count($token)==1&&isset($token[0]['id'])){
		return true;
	}
	return false;
}

/** Adds a password reset token **/
function addPasswordResetToken($mail){
	global $db;
	
	$token=substr(bin2hex(openssl_random_pseudo_bytes(16)), 0, 5); //random token
	$ip=$_SERVER['REMOTE_ADDR'];//ip of current user
	$user_id=$db->doQueryWithArgs("SELECT id FROM users WHERE mail=?", array($mail), "s")[0]["id"];
	
	if($db->doQueryWithArgs("INSERT INTO reset_tokens(user_id, token, ip) VALUES(?, ?, ?)", array($user_id, $token, $ip), 'iss')==true){
		return $token;
	}
	
	return false;
}

/**Removes old reset tokens **/
function clearResetTokens(){
	global $db;
	$db->doQueryWithoutArgs("DELETE FROM reset_tokens WHERE timestamp < (NOW() - INTERVAL 10 MINUTE)");
}
/**Removes unverified users after 10 minutes **/
function clearUnverifiedUsers(){
	global $db;
	$db->doQueryWithoutArgs("DELETE FROM users WHERE user_created < (NOW() - INTERVAL 10 MINUTE)");
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

/** Check if current user has capability **/
function currentUserCan($capability, $group_id){
	global $db, $capabilityCache;
	
	$caps=array();
	
	if(!isset($capabilityCache)||!is_array($capabilityCache)){
		$capabilityCache=array();
	}
	
	if(isset($capabilityCache[getUser()][$group_id]){
		$caps=$capabilityCache[getUser()][$group_id];
	}else{
		$caps=$db->doQueryWithArgs("SELECT capabilities FROM v_user_caps WHERE user_id=? AND group_id=?", array(getUser()["id"], $group_id), "ii")[0]["capabilities"];
		$caps=explode(",", $caps);
		$capabilityCache[getUser()][$group_id]=$caps;
	}
	
	return in_array($capability, $caps);
}
	
?>