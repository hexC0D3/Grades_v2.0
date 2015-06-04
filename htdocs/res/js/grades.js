
"use strict";

/* Global Storage */

if(typeof(Storage) === "undefined"){
	alert("Your browser is too old! Upgrade it!");
	throw new Error("Browser Version too old!");
}


function grades_loading(state){
	if(state == true){
		
	}else{
		
	}
}

function grades_validateAPIResponse(json){
	
	grades_loading(false);
	
	if(json.errors.length == 0){
		return true;
	}else{
		error(json.errors);
		return false;
	}
}

function error(error){
	console.log("ERROR:");
	console.log(error);
}

function grades_add(){
	
}

/* Angular */

var grades = angular.module('grades', ['ngRoute', 'noCAPTCHA', 'ngStorage', 'ngLocalize', 'ui.bootstrap']);

/* App */

/* Services */

/* Config */

grades
	.value('localeConf', {
		basePath: '/res/lang',
		defaultLocale: 'de-DE',
		sharedDictionary: 'common',
		fileExtension: '.lang.json',
		persistSelection: true,
		cookieName: 'COOKIE_LOCALE_LANG',
		observableAttrs: new RegExp('^data-(?!ng-|i18n)'),
		delimiter: '::'
	})
	.value('localeSupported', [
		'de-DE',
		'en-GB',
		'fr-FR',
		'it-IT'
	])
	.value('localeFallbacks', {
		'de'		: 'de-DE',
		'de-CH' 	: 'de-DE',
	    'en'		: 'en-GB',
	    'fr'		: 'fr-FR',
	    'it'		: 'it-IT'
	})
;



grades.config(['noCAPTCHAProvider', '$httpProvider', '$routeProvider', '$locationProvider', function (noCaptchaProvider, $httpProvider, $routeProvider, $locationProvider) {
	noCaptchaProvider.setSiteKey('6Le-hQITAAAAADiKBpBGQdALRYombGChKCjF23OP');
	noCaptchaProvider.setTheme('light');
	
	$httpProvider.defaults.transformRequest = function(data){
        if (data === undefined) {
            return data;
        }
        return $.param(data);
    }
    $httpProvider.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded; charset=UTF-8';
    
    
    $routeProvider.
    	when('/group/about/:groupID', {
	    	templateUrl: 'res/html/group/group.html'
    	}).
		when('/:name*', {
			templateUrl: function(urlAttr){
				return 'res/html/' + urlAttr.name + '.html';
			}
		}).
		otherwise({
			templateUrl: 'res/html/start.html'
		});
		
	$locationProvider.html5Mode(true);
		
}]);

/* Controllers */

grades.controller("AppController", ['$scope', '$http', '$sessionStorage', function($scope, $http, $sessionStorage) {
	
	var $me = this;
	
	this.$storage = $sessionStorage;
	
	this.$storage.pageTitle			=		'-';
	this.$storage.apiURL			=		'http://grades.dev/api/v1';
	
	if(typeof this.$storage.user === "undefined"){
		this.$storage.user = {};
	}
	
	if(typeof this.$storage.activeNavigation === "undefined"){
		this.$storage.activeNavigation = 0;
	}
	
	
	$scope.setTitle = function(title){
		$me.$storage.pageTitle = title;
	};
	
	$scope.title = function(){
		return "Grades" + ($me.$storage.pageTitle == "" ? "" : " - " + $me.$storage.pageTitle);
	};
	
	$scope.join_group  = function(group_id, member_id, member_type_id, callback){
		
		$http.put($me.$storage.apiURL+"/group/" + group_id + "/join/?" + jQuery.param({session_token:$me.$storage.sessionToken}), {member_type_id:member_type_id, member_id:member_id}).
					
			success(function(data, status, headers, config) {
				
				data = angular.fromJson(data);
				
				if(grades_validateAPIResponse(data)){
					callback(data);
				}
			});
		
	}
	$scope.leave_group = function(group_id, member_id, member_type_id, callback){
		
		
		$http.put($me.$storage.apiURL+"/group/" + group_id + "/leave/?" + jQuery.param({session_token:$me.$storage.sessionToken}), {member_type_id:member_type_id, member_id:member_id}).
					
			success(function(data, status, headers, config) {
				
				data = angular.fromJson(data);
				
				if(grades_validateAPIResponse(data)){
					callback(data);
				}
			});
			
	}
	
}]);

/* Header */

grades.controller("NavigationController", ['$http', '$sessionStorage', function($http, $sessionStorage) {
	
	var $me = this;
	
	this.$storage = $sessionStorage;
	
	this.isNavigation = function (nav){
		if(typeof nav !== "undefined"){
			return (nav == $me.$storage.activeNavigation);
		}else{
			return false;
		}
	};
}]);

/* Login */

grades.controller("LoginController", ['$scope', '$http', '$location', '$sessionStorage', function($scope, $http, $location, $sessionStorage) {
	
	var $me = this;
	
	this.$storage = $sessionStorage;
	
	this.mail = "";
	this.password = "";
	
	this.login = function(){
		if($me.mail != ""){
			if($me.password != ""){
				
				grades_loading(true);
				
				$http.post($me.$storage.apiURL+"/user/"+$me.mail+"/login", {password: $me.password}).
				
				success(function(data, status, headers, config) {
					
					data = angular.fromJson(data);
					
					if(grades_validateAPIResponse(data)){
						$me.$storage.sessionToken = data.session_token;
						
						$me.$storage.user = data.user;
						
						$location.path('/dashboard');
						$me.$storage.activeNavigation = 1;
					}
				});
				
			}else{
				error("Das Password-Feld sollte nicht leer sein!");
			}
		}else{
			error("Das Mail-Feld sollte nicht leer sein!");
		}
	}
}]);

/* Register */

grades.controller("RegisterController", ['$scope', '$http', '$location', '$sessionStorage', function($scope, $http, $location, $sessionStorage) {
	
	var $me = this;
	
	this.$storage = $sessionStorage;
	
	this.mail			= "";
	this.captcha		= "";
	this.code			= "";
	this.password		= "";
	this.password1		= "";
	
	this.step = 0;
	
	this.register = function(){
		if($me.mail != ""){
			if($me.captcha != "" && $me.captcha != false && $me.captcha != null){
				
				$http.post($me.$storage.apiURL+"/user/", {mail: $me.mail, captcha: $me.captcha}).
				
				success(function(data, status, headers, config) {
					
					data = angular.fromJson(data);
					
					if(grades_validateAPIResponse(data)){
						
						$me.step = 1;
						
					}
				});
				
			}else{
				error("Füllen Sie den Captcha aus!");
			}
		}else{
			error("Das Mail-Feld sollte nicht leer sein!");
		}
	}
	this.confirm = function(){
		if($me.mail != ""){
			if($me.code != ""){
				if($me.password != ""){
					if($me.password == $me.password1){
						$http.post($me.$storage.apiURL+"/user/"+$me.mail+"/verify", {code: $me.code, password: $me.password}).
					
						success(function(data, status, headers, config) {
							
							data = angular.fromJson(data);
							
							if(grades_validateAPIResponse(data)){
								$location.path('/dashboard');
								$me.$storage.activeNavigation = 1;
							}
						});
					}else{
						error("Die beiden Passwörter sollten übereinstimmen!");
					}
				}else{
					error("Das Passwort-Feld sollte nicht leer sein!");
				}
			}else{
				error("Das Code-Feld sollte nicht leer sein!");
			}
		}else{
			error("Das Mail-Feld sollte nicht leer sein!");
		}
	}
}]);

/* Dashboard */

grades.controller("DashboardController", function(){
	
});

/* Groups */

grades.controller("GroupAddController", ['$scope', '$http' ,'$sessionStorage', '$location', function($scope, $http, $sessionStorage, $location){

	var $me = this;
	
	this.$storage = $sessionStorage;
	
	this.name			= "";
	this.group_type		=  3;
	this.parentID		= -1;
	this.inviteOnly	= false;
	this.settings = [];
	
	this.parents = [];
	
	this.parent_relation = {
		1:-1,
		2:1,
		3:2,
		4:3,
		5:3
	};
	
	this.create = function(){
		if(! isNaN($me.group_type)){
			
			if($me.name.length > 0){
				
				$http.post($me.$storage.apiURL+"/group/", {group_name:$me.name, parent_group_id:$me.parentID, group_type_id:$me.group_type, invite_only: $me.inviteOnly}).
					
					success(function(data, status, headers, config) {
						
						data = angular.fromJson(data);
						
						if(grades_validateAPIResponse(data)){
							
							$location.path('/group/about/'+data.group.id);
							
						}
					});
				
			}else{
				error("Gib der Gruppe einen Namen!");
			}
			
		}else{
			error("Ungültige Auswahl");
		}
	};
	
	this.refresh = function(){
		$me.updateParents();
		$me.updateFields();
	};
	
	this.updateParents = function(){
		
		if($me.parent_relation[$me.group_type] != "-1"){
			
			$http.get($me.$storage.apiURL+"/group/?" + jQuery.param({filters: {group_type_id: $me.parent_relation[$me.group_type]}, session_token:$me.$storage.sessionToken})).
					
				success(function(data, status, headers, config) {
					
					data = angular.fromJson(data);
					
					if(grades_validateAPIResponse(data)){
						$me.parents = data.groups;
						$me.parentID = -1;
					}
				});
			
		}
	};
	
	this.updateFields = function(){
		
		$http.get($me.$storage.apiURL+"/group/?" + jQuery.param({filters: {group_type_id: $me.group_type, type: 'settings'}, session_token:$me.$storage.sessionToken})).
				
			success(function(data, status, headers, config) {
				
				data = angular.fromJson(data);
				
				if(grades_validateAPIResponse(data)){
					
					for(var i=0;i<data.length;i++){
						data[i].options = angular.fromJson(data[i].options);
					}
					
					$me.settings = data.settings;
					
				}
			});
		
	};
	
	$me.refresh();
	
}]);

grades.controller("GroupController", ['$scope', '$http' ,'$sessionStorage', '$routeParams', function($scope, $http, $sessionStorage, $routeParams){
	
	var $me = this;
	
	this.$scope				= $scope;
	this.groupType 			= "";
	this.inviteOnly 		= "";
	this.name				= "";
	this.canJoin			= "";
	this.canLeave			= "";
	this.capabilities		= [];
	
	this.$storage = $sessionStorage;
	
	this.loadData = function(){
		if(typeof $routeParams.groupID !== "undefined" && (! isNaN($routeParams.groupID))){
		
			$http.get($me.$storage.apiURL+"/group/" + $routeParams.groupID + "/?" + jQuery.param({session_token:$me.$storage.sessionToken})).
						
				success(function(data, status, headers, config) {
					
					data = angular.fromJson(data);
					
					if(grades_validateAPIResponse(data)){
						$me.groupType		= data.group.group_type;
						$me.inviteOnly		= Boolean(data.group.inviteOnly);
						$me.name			= data.group.name;
						$me.canJoin			= data.group.can_join;
						$me.canLeave		= data.group.can_leave;
						$me.capabilities	= data.group.capabilities;
					}
				});
			
		}else{
			error("Keine gülitige ID!");
		}
	}
	
	this.join = function(){
		$me.$scope.$parent.$parent.join_group($routeParams.groupID, $me.$storage.user.id, 1, function(){
			$me.loadData();
		});
	}
	this.leave = function(){
		$me.$scope.$parent.$parent.leave_group($routeParams.groupID, $me.$storage.user.id, 1, function(){
			$me.loadData();
		});
	}
	
	this.loadData();
	
}]);