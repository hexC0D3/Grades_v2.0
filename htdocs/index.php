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
	  
		<title>{{title()}}</title>
		
		<base href="/">
		
		<link rel="stylesheet" href="res/css/grades.css" title="Grades CSS" type="text/css" media="all" charset="utf-8">
		
		<script src="res/js/modernizr.custom.js" charset="utf-8"></script>

		<script src="res/js/jquery.min.js" charset="utf-8"></script>
		
		<script src="res/js/angular.min.js" charset="utf-8"></script>
		
		<script src="res/js/angular-route.min.js" charset="utf-8"></script>
		
		<script src="res/js/angular-no-captcha.js" charset="utf-8"></script>
		
		<script src="res/js/angular-cookies.min.js" charset="utf-8"></script>
		
		<script src="res/js/angular-sanitize.min.js" charset="utf-8"></script>
		
		<script src="res/js/angular-localization.min.js" charset="utf-8"></script>
		
		<script src="res/js/ngStorage.min.js" charset="utf-8"></script>
		
		<script src="res/js/ui-bootstrap-tpls-0.13.0.min.js" charset="utf-8"></script>
		
		<script src="res/js/bootstrap.min.js" charset="utf-8"></script>
		<script src="res/js/moment.min.js" charset="utf-8"></script>
		<script src="res/js/bootstrap-datetimepicker.js" charset="utf-8"></script>
		<script src="res/js/fullcalendar.min.js" charset="utf-8"></script>
		<script src="res/js/lang-all.js" charset="utf-8"></script>
		<script src="res/js/angular-ui-calendar.js" charset="utf-8"></script>
		
		<script src="res/js/grades.js" charset="utf-8"></script>
		
	</head>
	<body>
		<header>
			<div class="container">
				<div class="nav">
					<a href="/"><h1>Grades</h1></a>
					<nav ng-controller="NavigationController as navigation">
						<ul class="nav-home" ng-show="navigation.isNavigation(0)">
							<li><a href="/about" i18n="common.about_us"></a></li>
							<li><a href="/register" i18n="common.register"></a></li>
							<li><a href="/login" i18n="common.login"></a></li>
						</ul>
						<ul class="nav-dashboard" ng-show="navigation.isNavigation(1)">
							<li><a href="#" i18n="common.groups"></a></li>
							<li><a href="#" i18n="common.grades"></a></li>
							<li><a href="#" i18n="common.excercises"></a></li>
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