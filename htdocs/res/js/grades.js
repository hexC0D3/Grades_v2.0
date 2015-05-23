function loading(state){
	if(state == true){
		
	}else{
		
	}
}

function validateAPIResponse(json){
	
	loading(false);
	
	if(json.errors.length == 0){
		return true;
	}else{
		/*Do stuff with the error messages*/
		return false;
	}
}

/* Angular */

var grades = angular.module('grades', ['ngRoute', 'noCAPTCHA']);

/* App */

/* Services */

grades.factory('AppService', function() {
  return {
	  page_title : '',
	  
	  activeNavigation : 0,
	  
	  api_url : "http://grades.dev/api/v1",
	  
	  session_token : "",

	  user : {
		  'username' : "-"
	  }
  };
});

/* Config */


grades.config(['noCAPTCHAProvider', function (noCaptchaProvider) {
	noCaptchaProvider.setSiteKey('6Le-hQITAAAAADiKBpBGQdALRYombGChKCjF23OP');
	noCaptchaProvider.setTheme('light');
}]);

grades.config(function ($httpProvider) {
    $httpProvider.defaults.transformRequest = function(data){
        if (data === undefined) {
            return data;
        }
        return $.param(data);
    }
    $httpProvider.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded; charset=UTF-8';
});

grades.config(['$routeProvider', function($routeProvider) {
		$routeProvider.
		when('/about', {
			templateUrl: 'res/html/about.html'
		}).
		when('/register', {
			templateUrl: 'res/html/register.html'
		}).
		when('/login', {
			templateUrl: 'res/html/login.html'
		}).
		when('/dashboard', {
			templateUrl: 'res/html/dashboard.html'
		}).
		otherwise({
			templateUrl: 'res/html/start.html'
		});
  }]);

/* Controllers */

grades.controller("AppController", ['AppService', '$http', function(AppService, $http) {
	
	this.setTitle = function(title){
		AppService.page_title = title;
	};
	
	this.title = function(){
		return "Grades" + (AppService.page_title == "" ? "" : " - "+AppService.page_title);
	};
	
}]);

/* Header */

grades.controller("NavigationController", ['AppService', '$http', function(AppService, $http) {
	
	this.isNavigation = function (nav){
		return nav == AppService.activeNavigation;
	};
}]);

/* Login */

grades.controller("LoginController", ['AppService', '$http', '$location', function(AppService, $http, $location) {
	this.mail = "";
	this.password = "";
	
	this.warning = "";
	
	this.login = function(){
		if(this.mail != ""){
			if(this.password != ""){
				
				loading(true);
				
				$http.post(AppService.api_url+"/user/"+this.mail+"/login", {password: this.password}).
				
				success(function(data, status, headers, config) {
					
					data = angular.fromJson(data);
					
					if(validateAPIResponse(data)){
						AppService.session_token = data.session_token;
						$location.path('/dashboard');
						AppService.activeNavigation = 1;
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

grades.controller("RegisterController", ['AppService', '$http', '$location', function(AppService, $http, $location) {
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
				
				$http.post(AppService.api_url+"/user/", {mail: this.mail, captcha: this.captcha}).
				
				success(function(data, status, headers, config) {
					
					data = angular.fromJson(data);
					
					if(validateAPIResponse(data)){
						
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
						$http.post(AppService.api_url+"/user/"+this.mail+"/verify", {code: this.code, password: this.password}).
					
						success(function(data, status, headers, config) {
							
							data = angular.fromJson(data);
							
							if(validateAPIResponse(data)){
								$location.path('/dashboard');
								AppService.activeNavigation = 1;
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