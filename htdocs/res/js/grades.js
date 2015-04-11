var grades = angular.module('grades', ['ngRoute']);

/* App */

/* Routing */

grades.config(['$routeProvider', function($routeProvider) {
		$routeProvider.
		when('/reviews', {
			templateUrl: 'res/html/reviews.html'
		}).
		when('/features', {
			templateUrl: 'res/html/features.html'
		}).
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

grades.controller("appController", function(){
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
	
});

/* Header */

grades.controller("navigationController", function(){
	
	this.activeNavigation = 0;
	
	this.isNavigation = function (nav){
		return nav == this.activeNavigation;
	};
	
	this.setNavigation = function(nav){
		this.activeNavigation = nav;
	};
});

/* Dashboard */

grades.controller("dashboardController", function(){
	
});

