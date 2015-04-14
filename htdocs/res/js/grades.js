var api = {
	url : "http://grades.dev/api/v1/",
	session_token : null
}

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

var grades = angular.module('grades', ['ngRoute']);

/* App */

/* Routing */

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

grades.controller("AppController", ['$http', function($http) {
	this.user = {
		'username' : '-'
	}
	
	this.subtitle = "";
	this.setTitle = function(title){
		this.subtitle = title;
	};
	
	this.title = function(){
		return "Grades" + (this.subtitle == "" ? "" : " - "+this.subtitle);
	};
	
}]);

/* Header */

grades.controller("NavigationController", ['$http', function($http) {
	
	this.activeNavigation = 0;
	
	this.isNavigation = function (nav){
		return nav == this.activeNavigation;
	};
	
	this.setNavigation = function(nav){
		this.activeNavigation = nav;
	};
}]);

/* Login */

grades.controller("LoginController", ['$http', function($http) {
	this.mail = "";
	this.password = "";
	
	this.warning = "";
	
	this.login = function(){
		if(this.mail != ""){
			if(this.password != ""){
				
				loading(true);
				
				$http.post(api.url+"users/"+mail+"/login", {password: password}).
				
				success(function(data, status, headers, config) {
					
					console.log(data);
					
					data = angular.fromJson(data);
					
					if(validateAPIResponse(data)){
						api.session_token = data.session_token;
						
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

grades.controller("RegisterController", ['$http', function($http) {
	this.mail = "";
	this.captcha = "";
	
	this.register = function(){
		
	}
}]);

/* Dashboard */

grades.controller("DashboardController", function(){
	
});