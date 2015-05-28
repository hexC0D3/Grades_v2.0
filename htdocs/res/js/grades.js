
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
		/*Do stuff with the error messages*/
		return false;
	}
}

function grades_add(){
	
}

/* Angular */

var grades = angular.module('grades', ['ngRoute', 'noCAPTCHA', 'ngStorage', 'ngLocalize']);

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

grades.controller("AppController", ['$http', '$sessionStorage', function($http, $sessionStorage) {
	
	$me = this;
	
	this.$storage = $sessionStorage;
	
	this.$storage.pageTitle			=		'-';
	this.$storage.activeNavigation	=		0;
	this.$storage.apiURL			=		'http://grades.dev/api/v1';
	this.$storage.username			=		'-';
	this.$storage.sessionToken		=		'-';
	
	
	this.setTitle = function(title){
		$me.$storage.pageTitle = title;
	};
	
	this.title = function(){
		return "Grades" + ($me.$storage.pageTitle == "" ? "" : " - " + $me.$storage.pageTitle);
	};
	
}]);

/* Header */

grades.controller("NavigationController", ['$http', '$sessionStorage', function($http, $sessionStorage) {
	
	$me = this;
	
	this.$storage = $sessionStorage;
	
	this.isNavigation = function (nav){
		return nav == $me.$storage.activeNavigation;
	};
}]);

/* Login */

grades.controller("LoginController", ['$http', '$location', '$sessionStorage', function($http, $location, $sessionStorage) {
	
	$me = this;
	
	this.$storage = $sessionStorage;
	
	this.mail = "";
	this.password = "";
	
	this.warning = "";
	
	this.login = function(){
		if(this.mail != ""){
			if(this.password != ""){
				
				grades_loading(true);
				
				$http.post($me.$storage.apiURL+"/user/"+this.mail+"/login", {password: this.password}).
				
				success(function(data, status, headers, config) {
					
					data = angular.fromJson(data);
					
					if(grades_validateAPIResponse(data)){
						$me.$storage.sessionToken = data.sessionToken;
						$location.path('/dashboard');
						$me.$storage.activeNavigation = 1;
					}
				});
				
			}else{
				this.warning = "Das Password-Feld sollte nicht leer sein!";
			}
		}else{
			this.warning = "Das Mail-Feld sollte nicht leer sein!";
		}
	}
}]);

/* Register */

grades.controller("RegisterController", ['$http', '$location', '$sessionStorage', function($http, $location, $sessionStorage) {
	
	$me = this;
	
	this.$storage = $sessionStorage;
	
	this.mail = "";
	this.captcha = "";
	this.warning = "";
	this.code = "";
	this.password = "";
	this.password1 = "";
	
	$me = this;
	
	this.step = 0;
	
	this.register = function(){
		if(this.mail != ""){
			if(this.captcha != "" && this.captcha != false && this.captcha != null){
				
				$http.post($me.$storage.apiURL+"/user/", {mail: this.mail, captcha: this.captcha}).
				
				success(function(data, status, headers, config) {
					
					data = angular.fromJson(data);
					
					if(grades_validateAPIResponse(data)){
						
						$me.step = 1;
						
					}
				});
				
			}else{
				this.warning = "Füllen Sie den Captcha aus!";
			}
		}else{
			this.warning = "Das Mail-Feld sollte nicht leer sein!";
		}
	}
	this.confirm = function(){
		if(this.mail != ""){
			if(this.code != ""){
				if(this.password != ""){
					if(this.password == this.password1){
						$http.post($me.$storage.apiURL+"/user/"+this.mail+"/verify", {code: this.code, password: this.password}).
					
						success(function(data, status, headers, config) {
							
							data = angular.fromJson(data);
							
							if(grades_validateAPIResponse(data)){
								$location.path('/dashboard');
								$me.$storage.activeNavigation = 1;
							}
						});
					}else{
						this.warning = "Die beiden Passwörter sollten übereinstimmen!";
					}
				}else{
					this.warning = "Das Passwort-Feld sollte nicht leer sein!";
				}
			}else{
				this.warning = "Das Code-Feld sollte nicht leer sein!";
			}
		}else{
			this.warning = "Das Mail-Feld sollte nicht leer sein!";
		}
	}
}]);

/* Dashboard */

grades.controller("DashboardController", function(){
	
});