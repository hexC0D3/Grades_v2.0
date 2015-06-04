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
	global $user, $db, $SESSION_TOKEN;
	
	if(!empty($user)){
		return $user;
	}
	
	if($SESSION_TOKEN){
		
		$tmp = $db->doQueryWithArgs("SELECT users.id, users.mail, GROUP_CONCAT(user_meta_options.option_key) as option_keys, GROUP_CONCAT(user_meta.value) as 'values' FROM users LEFT JOIN user_meta ON users.id=user_meta.user_id LEFT JOIN user_meta_options ON user_meta.user_meta_option_id = user_meta_options.id LEFT JOIN login_tokens ON users.id=login_tokens.user_id WHERE login_tokens.login_token=?", 
		
		array($SESSION_TOKEN), "s");
		
		if(isset($tmp[0])){
			
			$keys = explode(",", $tmp[0]['option_keys']);
			$values = explode(",", $tmp[0]['values']);
			
			unset($tmp[0]['option_keys']);
			unset($tmp[0]['values']);
			
			for($i=0;$i<count($keys);$i++){
				$tmp[0][$keys[$i]] = $values[$i];
			}
			
			return $user = $tmp[0];
			
		}else{
			return null;
		}
		
	}else{
		return null;
	}
}

/** Logs in a user **/
function logInUser($mail, $password){
	global $db;
	
	$user = $db->doQueryWithArgs("SELECT * from users WHERE mail=?", array($mail), "s");
	
	if(!empty($user)&&$user!=false&&count($user)==1){
		
		$user=$user[0];
		
		if(password_verify($password, $user['password'])){
			//generate unique login token
			$token=bin2hex(openssl_random_pseudo_bytes(16));
			while($db->doQueryWithArgs("SELECT COUNT(*) as count FROM login_tokens WHERE login_token=?", array($token), "s")[0]["count"]>0){
				$token=bin2hex(openssl_random_pseudo_bytes(16));
			}
			
			$db->doQueryWithArgs("DELETE FROM login_tokens WHERE user_id=?", array($user["id"]), "i");
			
			$db->doQueryWithArgs("INSERT INTO login_tokens(user_id, login_token, ip) VALUES(?, ?, ?)", array($user["id"], $token, $_SERVER['REMOTE_ADDR']), "iss");
			
			global $JSON;
			
			$JSON['session_token'] = $token;
			
			
			$user_info = $db->doQueryWithArgs("SELECT users.id, users.mail, GROUP_CONCAT(user_meta_options.option_key) as option_keys, GROUP_CONCAT(user_meta.value) as 'values' FROM users LEFT JOIN user_meta ON users.id=user_meta.user_id LEFT JOIN user_meta_options ON user_meta.user_meta_option_id = user_meta_options.id WHERE users.id=?", array($user['id']), "s");
			
			$JSON['user'] = $user_info[0];
			
			return true;
		}else{
			addError(getMessages()->ERROR_SET_PASSWORD_INVALID_LOGIN); /*Wrong pword */
		}
	}
	addError(getMessages()->ERROR_SET_PASSWORD_INVALID_LOGIN); /* Wrong uname */
	
	return false;
}

/** Checks if a user already exists or not **/
function isMailRegistered($mail){
	
	if(filter_var($mail, FILTER_VALIDATE_EMAIL)){
		global $db;
		$data=$db->doQueryWithArgs("SELECT * FROM users WHERE mail=?", array($mail), "s");
		if(count($data) == 0){
			//everything is fine, mail not registered
			return false;
		}else if(count($data) == 1){
			//mail already registered
			if($data[0]['password'] == 'registering'){
				//user isn't verified by mail yet
				addError(getMessages()->ERROR_REGISTER_MAIL_ALREADY_IN_USE_NOT_VERIFIED);
			}else{
				//user is verified
				addError(getMessages()->ERROR_REGISTER_MAIL_ALREADY_IN_USE_VERIFIED);
			}
		}else{
			addError(getMessages()->UNKNOWN_ERROR(2));
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
					
					return true;
				}else{
					addError(getMessages()->UNKNOWN_ERROR(3));
				}
			}else{
				addError(getMessages()->ERROR_GEN_TOKEN);
			}
		}else{
			addError(getMessages()->ERROR_REGISTER_INVALID_CAPTCHA);
		}
	}
	
	clearResetTokens();
	clearUnverifiedUsers();
	
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
			if($db->doQueryWithArgs("UPDATE users SET password = ? WHERE mail=?", array($password, $mail), "ss")==true){
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
	
	$token=$db->doQueryWithArgs("SELECT reset_tokens.id FROM reset_tokens LEFT JOIN users ON reset_tokens.user_id=users.id WHERE users.mail=? AND reset_tokens.token=? AND reset_tokens.ip=? ", array($mail, $token, $_SERVER['REMOTE_ADDR']), "sss");
	
	if(count($token) == 1 && isset($token[0]['id'])){
		
		$db->doQueryWithArgs("DELETE FROM reset_tokens WHERE id=?", array($token[0]['id']), "i");
		
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
	
	if(!isUserLoggedIn()){return false;}
	
	global $db, $capabilityCache;
	
	$caps=array();
	
	if(!isset($capabilityCache)||!is_array($capabilityCache)){
		$capabilityCache=array();
	}
	
	if(isset($capabilityCache[getUser()['id']][$group_id])){
		$caps = $capabilityCache[getUser()['id']][$group_id];
	}else{
		$caps = $db->doQueryWithArgs("SELECT caps FROM v_user_caps WHERE user_id=? AND group_id=?", array(getUser()["id"], $group_id), "ii");
		
		if(count($caps) == 1){
			
			$caps = $caps[0]['caps'];
		}else{
			$caps = "";
		}
		$caps=explode(",", $caps);
		$capabilityCache[getUser()['id']][$group_id]=$caps;
	}
	
	return in_array($capability, $caps);
}
	
?>