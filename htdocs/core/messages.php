<?php
	
	global $messages;
	$messages=new Messages();
	
	class Messages{
		
		public function Messages(){
			/*Fill vars with content by using gettext localization*/
			
			//Errors
				//E-Mail
				$this->ERROR_MAIL_INVALID=_("Invalid E-Mail address");
				
				//Login
				$this->ERROR_LOGIN_UNKNOWN=_("Unknown error while logging in, please contact our support!");
				
				//Register
				$this->ERROR_REGISTER_MAIL_ALREADY_IN_USE=_("The entered E-Mail address is already in use!");
				$this->ERROR_REGISTER_UNKNOWN=_("Unknown error while registering in, please contact our support!");
				
				//Token generation
				$this->ERROR_GEN_TOKEN=_("Unknown error while generating a token! Please contact our support!");
				
			//Mail messages
			
			$this->MAIL_SUPPORT_NAME=_("Grades - Support");
			
			$this->MAIL_GREETING=_("Hi");
			$this->MAIL_REGARDS=_("Regards")."<br/>"._("Your Grades Team");
			
				//Register
				$this->MAIL_REGISTER_SUBJECT=_("Grades Support - Registration");
				$this->MAIL_REGISTER_BODY_PART=_("You have successfully registered yourself in the grade management system 'Grades'. To complete your registration you just need to set your password with the following link.");
				$this->MAIL_REGISTER_BODY_LINK=_("Set Password");
						
				//Reset Password
				$this->MAIL_PASSWORD_RESET_SUBJECT=_("Grades Support - Password Reset");
				$this->MAIL_PASSWORD_RESET_BODY_PART=_("A reset of your password has been requested! If you still want to reset it, then please click on the following link.");
				$this->MAIL_PASSWORD_RESET_BODY_LINK=_("Reset Password");
		}
	}
	
	function getMessages(){
		global $messages;
		return $messages;
	}
?>