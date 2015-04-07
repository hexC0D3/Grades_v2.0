<?php
	
	global $messages;
	$messages=new Messages();
	
	class Messages{
		
		public function Messages(){
			/*Fill vars with content by using gettext localization*/
			
			//Errors
			
				//Request Method
				$this->ERROR_INVALID_REQUEST_METHOD=_("Invalid Request Method!");
					
				//E-Mail
				$this->ERROR_MAIL_INVALID=_("Invalid E-Mail address");
				
				//Login
				$this->ERROR_LOGIN_UNKNOWN=_("Unknown error while logging in!");
				
				//Register
				$this->ERROR_REGISTER_MAIL_ALREADY_IN_USE_VERIFIED=_("The entered E-Mail address is already in use and was verified!");
				$this->ERROR_REGISTER_MAIL_ALREADY_IN_USE_NOT_VERIFIED=_("The entered E-Mail address is already in use but wasn't verified! You may try again in 10 minutes.");
				$this->ERROR_REGISTER_UNKNOWN=_("Unknown error while registering in, please contact our support!");
				$this->ERROR_REGISTER_INVALID_CAPTCHA=_("Your captcha is invalid! Please re-enter it.");
				
				//Token generation
				$this->ERROR_GEN_TOKEN=_("Unknown error while generating a token! Please contact our support!");
				
				//API
				$this->ERROR_API_CHEATING=_("Hi script-kiddie! ");	
				$this->ERROR_API_REQUIRED_FIELDS=_("Please send all required fields");
				$this->ERROR_API_PRIVILEGES=_("You don't have the permission to do that!");	
				$this->ERROR_API_UNKNOWN=_("Unknown error! Please report this.");
				
					//Input
					$this->ERROR_API_INVALID_INPUT=_("The given input is invalid!");
					
					//Versions
					$this->ERROR_API_VERSIONS_INVALID=_("The requested api version doesn't exist!");
					
					//User
						//Set password
						$this->ERROR_SET_PASSWORD_INVALID_CODE=_("Your code is invalid! Please request a new one.");
							//Password restrictions
							$this->ERROR_SET_PASSWORD_STRENGTH_LENGTH=_("Your passwords needs to have more than 5 letters!");
						//List
						$this->ERROR_API_USER_LIST_ALL=_("You cannot list all users! Please use a filter.");
					//Groups
					$this->ERROR_GROUPS_ALREADY_MEMBER=_("You're already a member of this group!");
						
						//Capabilities
						$this->ERROR_GROUPS_CAPABILITY_ALREADY_ASSIGNED=_("This user already has the chosen capability!");
						$this->ERROR_GROUPS_CAPABILITY_NOT_REMOVEABLE=_("This user doesn't have the chosen capability!");
						
			//Texts
				//Input
				$this->TEXT_INPUT_MAIL=_("yourname@domain.tld");
				//Submit
				$this->TEXT_SUBMIT_REGISTERING=_("Register");
				
				//Static
					//Get Started
					$this->TEXT_STATIC_GET_STARTED=_("Get Started");
				
			//Mail messages
			
			$this->MAIL_SUPPORT_NAME=_("Grades - Support");
			
			$this->MAIL_GREETING=_("Hi");
			$this->MAIL_REGARDS=_("Regards")."<br/>"._("Your Grades Team");
			
				//Register
				$this->MAIL_REGISTER_SUBJECT=_("Grades Support - Registration");
				$this->MAIL_REGISTER_BODY_PART=_("You have successfully registered yourself in the grade management system 'Grades'. To complete your registration you just need enter the following verification code:");
						
				//Reset Password
				$this->MAIL_PASSWORD_RESET_SUBJECT=_("Grades Support - Password Reset");
				$this->MAIL_PASSWORD_RESET_BODY_PART=_("A reset of your password has been requested! If you still want to reset it, then please click on the following link.");
				$this->MAIL_PASSWORD_RESET_BODY_LINK=_("Reset Password");
				
			//Groups
				//Global group options
				$this->GROUP_OPTIONS_INVITE_ONLY_DESC=("Enable this if you want, that not all grade members can freely join your group.");
				
				//Capabilities
				$this->GROUP_CAPABILITIES_MANAGE_CAPS=("Gives a user the capability to manage the capabilities of users in this group.");
				$this->GROUP_CAPABILITIES_MANAGE_OPTIONS=("Gives a user the capability to manage the options of this group.");
				$this->GROUP_CAPABILITIES_INVITE_USERS=("Gives a user the capability to invite other users into this group.");
				$this->GROUP_CAPABILITIES_CREATE_EVENTS=("Gives a user the capability to create group-wide events.");
				$this->GROUP_CAPABILITIES_CREATE_SUBJECTS=("Gives a user the capability to create group-wide subjects.");
				
				
			//Dynamic
			
				//Group Options
				$this->DYNAMIC_GROUP_TYPE_OPTIONS_MARK_CALC_METHOD_DESC=("The method this school uses to calculate your average grade.");
				$this->DYNAMIC_GROUP_TYPE_OPTIONS_CLASS_ADMIN_DESC=("The member of this class who's able to manage it.");
				$this->DYNAMIC_GROUP_TYPE_OPTIONS_WEBSITE_DESC=("This schools website URL.");
				$this->DYNAMIC_GROUP_TYPE_OPTIONS_ADDRESS_DESC=("This schools address.");
				$this->DYNAMIC_GROUP_TYPE_OPTIONS_SCHOOL_ADMIN_DESC=("The member of this school who's able to manage it.");
				$this->DYNAMIC_GROUP_TYPE_OPTIONS_SUBJECT_ID=("The subject this sub-class is related to.");
		}
	}
	
	function getMessages(){
		global $messages;
		return $messages;
	}
?>