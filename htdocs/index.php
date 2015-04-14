<?php
	//setup some http headers
	
	$trusted_domains = array('grades.dev', 'grades.hexcode.ch', 'grades.swiss');
	
	foreach($trusted_domains as $domain){
		//until we got a valid ssl certificate allow http and https
		header('Access-Control-Allow-Origin: http://'.$domain);
		header('Access-Control-Allow-Origin: http://'.$domain);
	}
	
?>
<!DOCTYPE html>
<html ng-app="grades" ng-controller="AppController as app">
	<head>
		
		<meta charset="utf-8">
	  
		<title>{{app.title()}}</title>
		
		<script src="res/js/modernizr.custom.js" charset="utf-8"></script>
		<script src="res/js/jquery.min.js" charset="utf-8"></script>
		<script src="res/js/angular.min.js" charset="utf-8"></script>
		<script src="res/js/angular-route.min.js" charset="utf-8"></script>
		<script src="res/js/highcharts.js" charset="utf-8"></script>
		
		<script src="res/js/grades.js" charset="utf-8"></script>
		
		<script src='https://www.google.com/recaptcha/api.js'></script>
		
		<link rel="stylesheet" href="res/css/grades.css" title="Grades CSS" type="text/css" media="all" charset="utf-8">
		
	</head>
	<body>
		<header>
			<div class="container">
				<div class="nav">
					<a href="#/"><h1>Grades</h1></a>
					<nav ng-controller="NavigationController as navigation">
						<ul class="nav-home" ng-show="navigation.isNavigation(0)">
							<li><a href="#/about">Über Uns</a></li>
							<li><a href="#/register">Registrieren</a></li>
							<li><a href="#/login">Login</a></li>
						</ul>
						<ul class="nav-dashboard" ng-show="navigation.isNavigation(1)">
							<li><a href="#">Gruppen</a></li>
							<li><a href="#">Noten</a></li>
							<li><a href="#">Aufgaben</a></li>
							<li><a href="#">{{app.user.username}}</a></li>
						</ul>
					</nav>
				</div>
			</div>
		</header>
				
		<main class="container" ng-view>
			
		</main>

		<footer>
				
		</footer>
		
	</body>
	
</html>