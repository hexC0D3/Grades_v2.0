<?php
	
	global $messages;
	$messages=new Messages();
	
	class Messages{
		
		public function UNKNOWN_ERROR($code){
			return $this->ERROR_API_UNKNOWN." Code: ".$code;//15 not used yet
		}
		
		public function Messages(){
			/*Fill vars with content by using gettext localization*/
			
			//Errors
			
				//Request Method
				$this->ERROR_INVALID_REQUEST_METHOD=_("Invalid Request Method!");
					
				//E-Mail
				$this->ERROR_MAIL_INVALID=_("Invalid E-Mail address");
				
				//Register
				$this->ERROR_REGISTER_MAIL_ALREADY_IN_USE_VERIFIED=_("The entered E-Mail address is already in use and was verified!");
				$this->ERROR_REGISTER_MAIL_ALREADY_IN_USE_NOT_VERIFIED=_("The entered E-Mail address is already in use but wasn't verified! You may try again in 10 minutes");
				$this->ERROR_REGISTER_INVALID_CAPTCHA=_("Your captcha is invalid! Please re-enter it");
				
				//API
				$this->ERROR_API_CHEATING=_("Hi script-kiddie!");	
				$this->ERROR_API_REQUIRED_FIELDS=_("Please send all required fields");
				$this->ERROR_API_PRIVILEGES=_("You don't have the permission to do that!");	
				$this->ERROR_API_UNKNOWN=_("Unknown error! Please report this");
				
					//Input
					$this->ERROR_API_INVALID_INPUT=_("The given input is invalid!");
					
					//Versions
					$this->ERROR_API_VERSIONS_INVALID=_("The requested api version doesn't exist!");
					
					//User
					
						//Login
						
						$this->ERROR_SET_PASSWORD_INVALID_LOGIN=_("Login failed! Username or password is invalid!");
						
						//Set password
						$this->ERROR_SET_PASSWORD_INVALID_CODE=_("Your code is invalid! Please request a new one");
							//Password restrictions
							$this->ERROR_SET_PASSWORD_STRENGTH_LENGTH=_("Your passwords needs to have more than 5 letters!");
						//List
						$this->ERROR_API_USER_LIST_ALL=_("You cannot list all users! Please use a filter");
						
						//Settings
						
					//Groups
					$this->ERROR_GROUPS_ALREADY_MEMBER=_("You're already a member of this group!");
					$this->ERROR_GROUPS_ALREADY_EXISTS=_("This group name already exists!");
					$this->ERROR_GROUPS_PARENT_NOT_EXISTING=_("The given parent doesn't exist!");
					$this->ERROR_GROUPS_INVITE_ONLY=_("You can't join this group, it is invite only!");
						
						//Capabilities
						$this->ERROR_GROUPS_CAPABILITY_ALREADY_ASSIGNED=_("This user already has the chosen capability!");
						$this->ERROR_GROUPS_CAPABILITY_NOT_REMOVEABLE=_("This user doesn't have the chosen capability!");
					
						//List
						$this->ERROR_API_GROUPS_LIST_ALL=_("You cannot list all groups! Please use a filter");
					
					//Events
						//List
						$this->ERROR_API_EVENTS_LIST_ALL=_("You cannot list all events! Please use a filter");
						
					//Grades
					$this->ERROR_GRADES_ALREADY_EXISTS=_("You've already added a grade for this test, you need to update it.");
						
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
				$this->MAIL_PASSWORD_RESET_BODY_PART=_("A reset of your password has been requested! If you still want to reset it, then please click on the following link");
				$this->MAIL_PASSWORD_RESET_BODY_LINK=_("Reset Password");
				
			//Groups
				//Global group options
				$this->GROUP_OPTIONS_INVITE_ONLY_DESC=_("Enable this if you want nobody to join this group without invitation");
				$this->GROUP_OPTIONS_NAME_DESC=_("The name of this group");
				
				//Capabilities
				$this->GROUP_CAPABILITIES_MANAGE_CAPS=_("Gives a user the capability to manage the capabilities of users in this group");
				$this->GROUP_CAPABILITIES_MANAGE_OPTIONS=_("Gives a user the capability to manage the options of this group");
				$this->GROUP_CAPABILITIES_MANAGE_MEMBERS=_("Gives a user the capability to manage members in this group");
				
			//Events
				//Global event options
				$this->EVENT_OPTIONS_TITLE_DESC=_("The title of this event");
			
			//Grades
			$this->GRADE_OPTIONS_GRADE_DESC=_("The grade you received");
			$this->GRADE_OPTIONS_GRADE_TEST_DESC=_("The test related to this grade");
				
			//Dynamic
			
				//Group Options
				$this->DYNAMIC_USER_OPTIONS_MARK_CALC_METHOD_DESC=_("The method this school uses to calculate your average grade");
				
				$this->DYNAMIC_USER_OPTIONS_MARK_CALC_METHOD_0=_("Range: 1-6, Best:6");
				$this->DYNAMIC_USER_OPTIONS_MARK_CALC_METHOD_1=_("Range: 1-6, Best:6, Negative Points 1x");
				$this->DYNAMIC_USER_OPTIONS_MARK_CALC_METHOD_2=_("Range: 1-6, Best:6, Negative Points 2x");
				
				$this->DYNAMIC_GROUP_TYPE_OPTIONS_SUBJECT_GRADES_INCLUDED_AVERAGE=_("If the grades received in this subject, are used in the calculations for the average grade");
				
				$this->DYNAMIC_GROUP_TYPE_OPTIONS_WEBSITE_DESC=_("This schools website URL");
				$this->DYNAMIC_GROUP_TYPE_OPTIONS_ADDRESS_DESC=_("This schools address");
				$this->DYNAMIC_GROUP_TYPE_OPTIONS_SCHOOL_ADMIN_DESC=_("The member of this school who's able to manage it");
				
				//Subjects
				$this->DYNAMIC_GROUP_TYPE_OPTIONS_SUBJECT_NAME=_("Subject Name");
				
				//User Options
				$this->DYNAMIC_USER_OPTIONS_FIRST_NAME_DESC=_("Your First Name");
				$this->DYNAMIC_USER_OPTIONS_LAST_NAME_DESC=_("Your Last Name");
				
				$this->DYNAMIC_USER_OPTIONS_GENDER_DESC=_("Your Gender");
				$this->DYNAMIC_USER_OPTIONS_GENDER_MALE=_("Male");
				$this->DYNAMIC_USER_OPTIONS_GENDER_FEMALE=_("Female");
				
				$this->DYNAMIC_USER_OPTIONS_BIRTHDAY_DESC=_("Your Birthday");
				
				$this->DYNAMIC_USER_OPTIONS_ABOUT_DESC=_("About me");
				
				//Event Options
				$this->DYNAMIC_EVENT_TYPE_OPTIONS_TEST_LESSON_ID_DESC=_("The lesson this test takes place");
				$this->DYNAMIC_EVENT_TYPE_OPTIONS_TEST_DAY_DESC=_("The day this lesson takes place");
				$this->DYNAMIC_EVENT_TYPE_OPTIONS_TEST_GRADE_WEIGT_DESC=_("The grade weight this test has");
				
				$this->DYNAMIC_EVENT_TYPE_OPTIONS_REPETITION_INTERVAL_DESC=_("Repetition interval in weeks");
				$this->DYNAMIC_EVENT_TYPE_OPTIONS_REPETITION_INTERVAL_0_DESC=_("No Repetition, a one time lesson");
				$this->DYNAMIC_EVENT_TYPE_OPTIONS_REPETITION_INTERVAL_1_DESC=_("Every week");
				$this->DYNAMIC_EVENT_TYPE_OPTIONS_REPETITION_INTERVAL_2_DESC=_("Every second week");
				
				$this->DYNAMIC_EVENT_TYPE_OPTIONS_LESSON_SUBJECT_ID_DESC=_("The subject of this lesson");
				$this->DYNAMIC_EVENT_TYPE_OPTIONS_LESSON_TIME_FROM_DESC=_("The time this lesson starts");
				$this->DYNAMIC_EVENT_TYPE_OPTIONS_LESSON_TIME_TO_DESC=_("The time this lesson ends");
				
				$this->DYNAMIC_EVENT_TYPE_OPTIONS_TASK_TIME_LESSON=_("The lesson this task is scheduled on");
				$this->DYNAMIC_EVENT_TYPE_OPTIONS_TASK_DAY_DESC=_("The day until this task needs to be done");
				$this->DYNAMIC_EVENT_TYPE_OPTIONS_TASK_DESC=_("A task description");
				
				$this->DYNAMIC_EVENT_TYPE_OPTIONS_EVENT_FULL_DAY_DESC=_("Is this a full day event?");
				$this->DYNAMIC_EVENT_TYPE_OPTIONS_EVENT_TIME_FROM_DESC=_("The time this event starts");
				$this->DYNAMIC_EVENT_TYPE_OPTIONS_EVENT_TIME_TO_DESC=_("The time this event ends");	
				
				
		}
	}
	
	function getMessages(){
		global $messages;
		return $messages;
	}
?>