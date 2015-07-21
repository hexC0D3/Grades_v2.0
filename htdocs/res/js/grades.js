
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

var grades = angular.module('grades', ['ngRoute', 'noCAPTCHA', 'ngStorage', 'ngLocalize', 'ui.bootstrap', 'ui.calendar', 'smart-table']);

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
    	when('/group/edit/:groupID', {
	    	templateUrl: 'res/html/group/edit.html'
    	}).
    	when('/event/about/:eventID', {
	    	templateUrl: 'res/html/event/event.html'
    	}).
    	when('/event/edit/:eventID', {
	    	templateUrl: 'res/html/event/edit.html'
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

var appController = grades.controller("AppController", ['$scope', '$http', '$sessionStorage', function($scope, $http, $sessionStorage) {
	
	var $me = this;
	
	this.$storage = $sessionStorage;
	
	this.$storage.pageTitle			=		'-';
	this.$storage.apiURL			=		'http://grades.dev/api/v1';
	
	this.user = {};
	
	$scope.$on('$routeChangeSuccess', function(next, current) { 
		
		if(typeof $me.$storage.modalInstance !== "undefined" && $me.$storage.modalInstance !== null && typeof $me.$storage.modalInstance.close !== "undefined"){
			$me.$storage.modalInstance.close();
		}
		
	});
	
	this.updateUser = function(){
		
		var user = $me.$storage.user;
		
		if(typeof user.option_keys !== "undefined" && user.option_keys !== null){
			
			for(var i=0;i<user.option_keys.length;i++){
				user[user.option_keys[i]] = user.values[i];
			}
			
			delete user.option_keys;
			delete user.values;
			
		}
		
		$me.user = user;
	};
	
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
	
	$me.updateUser();
	
}]);

appController.directive("userInput", function(){
	return {
		restrict: 'E',
		scope: {
			inputName: '=inputName',
			inputPlaceholder: '=inputPlaceholder',
			inputValue: '=inputValue'
	    },
		templateUrl: '/res/html/directives/userInput.html'
	};
});

appController.directive("repeatFields", function(){
	return {
		restrict: 'E',
		scope: {
			fields: '=fields',
			parentGroupID: '=parentGroupId',
			repeatDoneCallback: '=repeatDoneCallback'
	    },
		templateUrl: '/res/html/directives/fields.html'
	};
});

appController.directive("groupInput", function(){
	return {
		restrict: 'E',
		scope: {
			inputName: '=inputName',
			inputPlaceholder: '=inputPlaceholder',
			inputTransPlaceholder: '=inputTransPlaceholder',
			inputType: '=inputType',
			inputModel: '=inputModel',
			inputValue: '=inputValue'
	    },
		templateUrl: '/res/html/directives/groupInput.html'
	};
});

appController.directive("eventInput", function(){
	return {
		restrict: 'E',
		scope: {
			inputName: '=inputName',
			inputPlaceholder: '=inputPlaceholder',
			inputTransPlaceholder: '=inputTransPlaceholder',
			inputType: '=inputType',
			inputValue: '=inputValue'
	    },
		templateUrl: '/res/html/directives/eventInput.html'
	};
});

appController.directive("dateInput", function(){
	return {
		restrict: 'E',
		scope: {
			inputName: '=inputName',
			inputPlaceholder: '=inputPlaceholder',
			inputTransPlaceholder: '=inputTransPlaceholder',
			inputType: '=inputType',
			inputValue: '=inputValue',
			inputTime: '=inputTime'
	    },
		templateUrl: '/res/html/directives/dateInput.html'
	};
});

appController.directive('repeatDone', function() {
    return function(scope, element, attrs) {
	    
        if (scope.$last) {
            scope.$eval(attrs.repeatDone);
        }
    }
});

appController.directive('defaultDate', function() {
    return {
        link: function($scope, element, attrs) {
            // Trigger when number of children changes,
            // including by directives like ng-repeat
            var watch = $scope.$watch(function() {
                return element.children().length;
            }, function() {
                // Wait for templates to render
                $scope.$evalAsync(function() {
                    // Finally, directives are evaluated
                    // and templates are renderer here
                    if(jQuery(element).attr("data-time") == "false"){
	                    jQuery(element).val(jQuery(".calendar").fullCalendar('getDate').format("DD. MM. YYYY"));
                    }else{
	                    var minute = moment().minute();
	                    
	                    jQuery(element).val(jQuery(".calendar").fullCalendar('getDate').minute(5 * Math.round( minute / 5 )).format("DD. MM. YYYY HH:mm"));   
                    }
                    
                });
            });
        },
    };
});

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

grades.controller("DashboardController", ['$scope', '$http' ,'$sessionStorage', '$routeParams', '$location', '$modal', function($scope, $http, $sessionStorage, $routeParams, $location, $modal){
	
	var $me = this;
	
	this.$scope	= $scope;
    
    this.static_events = [];
    this.repeating_events = [];
    
    this.eventSource = [];
    
    this.getRepeatingEvents = function(start, end, timezone, callback) {
	    
	    var events = [];
	    
	    for(var i=0;i < $me.repeating_events.length;i++){
		    
		    for (var d = start.toDate(); d <= end.toDate(); d.setDate(d.getDate() + ($me.repeating_events[i].repetition_interval * 7))) {
			    
			    
			    
    		}
		    
	    }
	    
	    callback(events);
	    
	};
	
	this.refreshEvents = function(){
		
		$http.get($me.$storage.apiURL+"/event/?" + jQuery.param({session_token:$me.$storage.sessionToken})).
					
			success(function(data, status, headers, config) {
				data = angular.fromJson(data);
				
				if(grades_validateAPIResponse(data)){
					
					$me.static_events		= [];
					$me.repeating_events	= [];
					
					for(var i=0;i<data.events.length;i++){
						
						var event = {
							id:			data.events[i].id,
							title:		data.events[i].title,
							editable:	data.events[i].canEdit,
							className:	['event-type-'+data.events[i].event_type]
						};
						
						if(data.events[i].event_type == 'event'){
							
							event.allDay		= data.events[i].options.event_full_day == true;
							event.start			= moment.unix(data.events[i].options.time_from);
							event.end			= moment.unix(data.events[i].options.time_to);
							
							$me.static_events.push(event);
							
						}else if(data.events[i].event_type == 'lesson'){
							event.allDay				= false;
							event.start					= moment.unix(data.events[i].options.time_from);
							event.end					= moment.unix(data.events[i].options.time_to);
							event.repetition_interval	= data.events[i].options.lesson_repetition_interval;
							
							$me.repeating_events.push(event);
						}
					}
				}
				
				jQuery(".calendar").fullCalendar('removeEvents');
				jQuery(".calendar").fullCalendar('addEventSource', $me.static_events);
				jQuery(".calendar").fullCalendar('addEventSource', $me.getRepeatingEvents);
				jQuery(".calendar").fullCalendar('rerenderEvents');
			});
		
	};
	
	/**/
	
	this.$storage = $sessionStorage;
	
	this.setTimeline = function () {
		
	    var parentDiv = jQuery(".fc-agenda-view:visible .fc-time-grid-container");
	    
	    if(jQuery(".fc-today").length > 0){
		    
		    var timeline = parentDiv.children(".timeline");
		    if (timeline.length == 0) { //if timeline isn't there, add it
		        timeline = jQuery("<hr>").addClass("timeline");
		        parentDiv.prepend(timeline);
		    }
		
		    var curTime = new Date();
		
		    var curCalView = jQuery(".calendar").fullCalendar('getView');
		
		    var curSeconds = (curTime.getHours() * 60 * 60) + (curTime.getMinutes() * 60) + curTime.getSeconds();
		    var percentOfDay = curSeconds / 86400; //24 * 60 * 60 = 86400, # of seconds in a day
		    var topLoc = Math.floor(parentDiv[0].scrollHeight * percentOfDay);
		
		    timeline.css("top", topLoc + "px");
		
		    if (curCalView.name == "agendaWeek") { //week view, don't want the timeline to go the whole way across
		        var dayCol = jQuery(".fc-today:visible");
		        var left = dayCol.position().left + 1;
		        var width = dayCol.width() - 2;
		        timeline.css({
		            left: left + "px",
		            width: width + "px"
		        });
		    }
		      
	    }
	
	};
	
	this.updateDashboardLayout = function(callback){
		
		var curCalView = jQuery(".calendar").fullCalendar('getView');
		if (curCalView.name == "agendaDay") {
			jQuery(".row.widgets").removeClass("expandCalendar");
			setTimeout(function(){
				jQuery(".widget").removeAttr("style");
			}, 1000);
		}else{
			jQuery(".widget").each(function(){
				jQuery(this).css("height", (jQuery(this).height()));
			}).promise().done(function(){
				jQuery(".row.widgets").addClass("expandCalendar");
			});
		}
		
		setTimeout(function(){
			callback();
		}, 1000);
	};
	
	this.calendar = {
		height: (jQuery(".widget.timetable").height()-40),
		defaultView: 'agendaDay',
		lang: 'de',
		timezone: 'local',
		timeFormat: 'H:mm',
		editable: true,
		header:{
			left: 'title,prev,next',
			center: '',
			right: 'agendaDay, agendaWeek, month'
		},
		eventClick: function(calEvent, jsEvent, view){
			
			if(calEvent.editable){
				
				$me.$storage.modalInstance = $modal.open({
					animation: true,
					controller: 'ModalController',
					templateUrl: '/res/html/event/edit.html',
					size: 'lg',
					resolve: {
						modalParams: function(){
							
							return {
								event_id: calEvent.id
							};
							
						}
					}
				});
				
			}else{
				
				$me.$storage.modalInstance = $modal.open({
					animation: true,
					controller: 'ModalController',
					templateUrl: '/res/html/event/event.html',
					size: 'lg',
					resolve: {
						modalParams: function(){
							
							return {
								event_id: calEvent.id
							};
							
						}
					}
				});
				
			}
			
			
			
		},
		dayClick: function(date, jsEvent, view){
			
			if(view.name == "agendaDay"){
				
				$me.$storage.modalInstance = $modal.open({
					animation: true,
					templateUrl: '/res/html/event/add.html',
					size: 'lg'
				});
				
			}else{
				jQuery('.calendar').fullCalendar('changeView', 'agendaDay');
				jQuery('.calendar').fullCalendar('gotoDate', date);
				
			}
			
		},
		eventMouseover: function(event, jsEvent, view){
			
		},
		eventMouseout: function(event, jsEvent, view){
			
		},
		eventDrop: function(event, delta, revertFunc, jsEvent, ui, view){
			
			$me.updateEvent(event, revertFunc);
		},
		eventResize: function(event, delta, revertFunc, jsEvent, ui, view){
			$me.updateEvent(event, revertFunc);
		},
		viewRender: function (view) {
	        
	        $me.updateDashboardLayout(function(){
		        try{
					$me.setTimeline();
			    }catch(err){
				    
				}
	        });
	        
	    },
    };
    
    this.updateEvent = function(event, revertFunc){
	    $http.put($me.$storage.apiURL+"/event/"+event.id+"/settings/?" + jQuery.param({session_token:$me.$storage.sessionToken}), {option_key: 'time_from', value: event.start.unix()}).
			
			success(function(data, status, headers, config) {
				data = angular.fromJson(data);
				
				if(!grades_validateAPIResponse(data)){
					revertFunc();
				}
			});
		
		var end;	
		
		if(typeof event.end !== "undefined" && event.end !== null){
			end = event.end.unix();
		}else{
			end = event.start.unix();
		}
		
		$http.put($me.$storage.apiURL+"/event/"+event.id+"/settings/?" + jQuery.param({session_token:$me.$storage.sessionToken}), {option_key: 'time_to', value: end}).
			
			success(function(data, status, headers, config) {
				data = angular.fromJson(data);
				
				if(!grades_validateAPIResponse(data)){
					revertFunc();
				}
			});
    }
    
	this.refreshEvents();
	
}]);

/* Add */

grades.controller("GroupAddController", ['$scope', '$http' ,'$sessionStorage', '$location', function($scope, $http, $sessionStorage, $location){

	var $me = this;
	
	this.$storage = $sessionStorage;
		
	this.name			= "";
	this.invite_only	= false;
	this.group_type		=  2;
	this.parentID		= -1;
		
	this.parents = [];
	
	this.settings = [];
		
	this.parent_relation = {
		1:-1,
		2:1,
		3:2
	};
	
	
	
	this.create = function($event){
		
		var formElement = angular.element($event.target);
		var settings = {};
		
		jQuery(formElement).find("[name]").each(function(index){
			
			var field_name = jQuery(this).attr("name").trim();
			
			if(jQuery(this).is("input[type='checkbox']")){
				if(field_name === 'invite_only'){
					$me.invite_only = jQuery(this).is(":checked");
				}else{
					settings[field_name] = jQuery(this).is(":checked");
				}
			}else if(field_name === 'name'){
				$me.name = jQuery(this).val();
			}else{
				settings[field_name] = jQuery(this).val();
			}
		});
		
		
		$http.post($me.$storage.apiURL+"/group/?" + jQuery.param({session_token:$me.$storage.sessionToken}), {group_name:$me.name, parent_group_id:$me.parentID, group_type_id:$me.group_type, invite_only: $me.invite_only, options: settings}).
					
			success(function(data, status, headers, config) {
				
				data = angular.fromJson(data);
				
				if(grades_validateAPIResponse(data)){
					
					$location.path('/group/about/'+data.group.id);
					
				}
			});
		
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
		
		var url = $me.$storage.apiURL+"/group/?" + jQuery.param({filters: {group_type_id: $me.group_type, type: 'settings'}, session_token:$me.$storage.sessionToken});
		
		$http.get(url).
				
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

grades.filter('to_trusted', ['$sce', function($sce){
    return function(text) {
        return $sce.trustAsHtml(text);
    };
}]);


grades.controller("GroupOverviewController", ['$scope', '$http' ,'$sessionStorage', '$location', '$routeParams', '$modal', function($scope, $http, $sessionStorage, $location, $routeParams, $modal){
	
	var $me = this;
	
	this.$storage = $sessionStorage;
	
	$scope.groupRows = [];
	$scope.displayedGroupRows = [].concat($scope.groupRows);
	
	this.page = 0;
	this.items_per_page = 30;
	
	this.updateGroups = function(){
		
		$http.get($me.$storage.apiURL+"/group/?" + jQuery.param({filters:{items_per_page: $me.items_per_page, page:$me.page},session_token:$me.$storage.sessionToken})).
				
			success(function(data, status, headers, config) {
				
				data = angular.fromJson(data);
				
				if(grades_validateAPIResponse(data)){
					
					$scope.groupRows = data.groups;
					
				}
			});
		
	};
	
	this.about = function(group_id){
		
		$me.$storage.modalInstance = $modal.open({
			animation: true,
			controller: 'ModalController',
			templateUrl: '/res/html/group/group.html',
			size: 'lg',
			resolve: {
				modalParams: function(){
					
					return {
						group_id: group_id
					};
					
				}
			}
		});
		
	};
	
	this.addGroup = function(){
		
		$location.path('/group/add');
		
	};
	
	this.updateGroups();
	
}]);


grades.controller("GroupEditController", ['$scope', '$http' ,'$sessionStorage', '$location', '$routeParams', '$timeout', function($scope, $http, $sessionStorage, $location, $routeParams, $timeout){
	
	var $me = this;
	
	this.$storage = $sessionStorage;
	
	this.groupType 			= "";
	this.inviteOnly 		= "";
	this.name				= "";
	this.canJoin			= "";
	this.canLeave			= "";
	this.capabilities		= [];
	
	$scope.settings = [];
	
	this.group_id = -1;
	
	if(typeof $routeParams.groupID !== "undefined" && (! isNaN($routeParams.groupID))){
		
		this.group_id = $routeParams.groupID;
		
	}else if(typeof $scope.$parent.modalParams.group_id !== "undefined"){
		
		this.group_id = $scope.$parent.modalParams.group_id;
		
	}
	
	this.fillData = function(){
		
		$scope.$evalAsync(function(){
			
			jQuery("[name="+$scope.settings[$scope.settings.length -1].key+"]").waitUntilExists(function(){
				
				var field;
				
				for(var i=0;i<$scope.settings.length;i++){
					field = jQuery("[name="+$scope.settings[i].key+"]");
					if(field.is("input[type='checkbox']")){
						field.prop('checked', ($scope.settings[i].value == true));
					}else{
						field.val($scope.settings[i].value);
					}
				}
				
			});
		});
	};
	
	this.repeatDone = function(){
		$me.fillData();
	};
	
	this.update = function($event){
		
		var formElement = angular.element($event.target);
		var settings = {};
		
		jQuery(formElement).find("[name]").each(function(index){
			
			var field_name = jQuery(this).attr("name").trim();
			
			if(jQuery(this).is("input[type='checkbox']")){
				settings[field_name] = jQuery(this).is(":checked");
			}else{
				settings[field_name] = jQuery(this).val();
			}
		});
		
		for (var key in settings) {

			if (settings.hasOwnProperty(key)) {
		          
				$http.put($me.$storage.apiURL+"/group/"+$me.group_id+"/settings/?" + jQuery.param({session_token:$me.$storage.sessionToken}), {option_key:key, value:settings[key]}).
			
					success(function(data, status, headers, config) {
						
						data = angular.fromJson(data);
						
						if(grades_validateAPIResponse(data)){
							
							return true;
	
						}
					});
		    }
		}
		
	};
	
	this.updateFields = function(){
		
		$http.get($me.$storage.apiURL+"/group/" + $me.group_id + "/?" + jQuery.param({session_token:$me.$storage.sessionToken})).
					
			success(function(data, status, headers, config) {
				
				data = angular.fromJson(data);
				
				if(grades_validateAPIResponse(data)){
					$me.groupType		= data.group.group_type;
					$me.inviteOnly		= data.group.inviteOnly == true;
					$me.name			= data.group.name;
					$me.canJoin			= data.group.can_join;
					$me.canLeave		= data.group.can_leave;
					$me.capabilities	= data.group.capabilities;
				}
			});
		
		$http.get($me.$storage.apiURL+"/group/"+$me.group_id+"/settings/?" + jQuery.param({session_token:$me.$storage.sessionToken})).
				
			success(function(data, status, headers, config) {
				
				data = angular.fromJson(data);
				
				if(grades_validateAPIResponse(data)){
					
					$scope.settings = data.settings;
					
				}
			});
		
	};
	
	$me.updateFields();
	
	
}]);

grades.controller("GroupController", ['$scope', '$http' ,'$sessionStorage', '$routeParams', '$location', function($scope, $http, $sessionStorage, $routeParams, $location){
	
	var $me = this;
	
	this.$scope				= $scope;
	this.groupType 			= "";
	this.inviteOnly 		= "";
	this.name				= "";
	this.canJoin			= "";
	this.canLeave			= "";
	this.capabilities		= [];
	
	this.group_id = -1;
	
	if(typeof $routeParams.groupID !== "undefined" && (! isNaN($routeParams.groupID))){
		
		this.group_id = $routeParams.groupID;
		
	}else if(typeof $scope.$parent.modalParams.group_id !== "undefined"){
		
		this.group_id = $scope.$parent.modalParams.group_id;
		
	}
	
	this.$storage = $sessionStorage;
	
	this.loadData = function(){
		
		$http.get($me.$storage.apiURL+"/group/" + $me.group_id + "/?" + jQuery.param({session_token:$me.$storage.sessionToken})).
					
			success(function(data, status, headers, config) {
				
				data = angular.fromJson(data);
				
				if(grades_validateAPIResponse(data)){
					$me.groupType		= data.group.group_type;
					$me.inviteOnly		= data.group.inviteOnly == true;
					$me.name			= data.group.name;
					$me.canJoin			= data.group.can_join;
					$me.canLeave		= data.group.can_leave;
					$me.capabilities	= data.group.capabilities;
				}
			});
	}
	
	this.join = function(){
		$me.$scope.$parent.$parent.join_group($me.group_id, $me.$storage.user.id, 1, function(){
			$me.loadData();
		});
	}
	this.leave = function(){
		$me.$scope.$parent.$parent.leave_group($me.group_id, $me.$storage.user.id, 1, function(){
			$me.loadData();
		});
	}
	
	this.canEdit = function(){
		
		return $me.capabilities.indexOf('manage_options') != -1;
		
	};
	
	this.edit = function(){
		$location.path('/group/edit/'+$me.group_id);
	};
	
	this.loadData();
	
}]);

grades.controller("ModalController", ['$scope', '$http' ,'$sessionStorage', '$routeParams', '$location', 'modalParams', function($scope, $http, $sessionStorage, $routeParams, $location, modalParams){
	
	$scope.modalParams = modalParams;
		
}]);

grades.controller("EventController", ['$scope', '$http' ,'$sessionStorage', '$routeParams', '$location', function($scope, $http, $sessionStorage, $routeParams, $location){
	
	var $me = this;
	
	this.$scope				= $scope;
	this.eventType 			= "";
	this.title				= "";
	
	this.event_id = -1;
		
	if(typeof $routeParams.eventID !== "undefined" && (! isNaN($routeParams.eventID))){
		
		this.event_id = $routeParams.eventID;
		
	}else if(typeof $scope.$parent.modalParams.event_id !== "undefined"){
		
		this.event_id = $scope.$parent.modalParams.event_id;
		
	}
	
	this.$storage = $sessionStorage;
	
	this.loadData = function(){
			
		$http.get($me.$storage.apiURL+"/event/" + $me.event_id + "/?" + jQuery.param({session_token:$me.$storage.sessionToken})).
					
			success(function(data, status, headers, config) {
				
				data = angular.fromJson(data);
				
				if(grades_validateAPIResponse(data)){
					
					console.log(data);
					
					$me.title				= data.event.title;
					$me.eventType			= data.event.event_type;
					
				}
			});
		
		
	}
	
	this.loadData();
	
}]);

grades.controller("EventAddController", ['$scope', '$http' ,'$sessionStorage', '$location', function($scope, $http, $sessionStorage, $location){
	
	var $me = this;
	
	this.title = "";
	this.parentID = "";
	this.settings = [];
	this.event_type = 1;
	
	this.$storage = $sessionStorage;
	
	
	this.create = function($event){
		
		var formElement = angular.element($event.target);
		var settings = {};
		
		jQuery(formElement).find("[name]").each(function(index){
			
			var field_name = jQuery(this).attr("name").trim();
			
			if(jQuery(this).is("input[type='checkbox']")){
				settings[field_name] = jQuery(this).is(":checked");
			}else if(field_name === 'title'){
				$me.title = jQuery(this).val();
			}else if(jQuery(this).is(".datepicker")){
				settings[field_name] = moment(jQuery(this).val(), "DD. MM. YYYY HH:mm").unix();
			}else{
				settings[field_name] = jQuery(this).val();
			}
		});
		
		$http.post($me.$storage.apiURL+"/event/?" + jQuery.param({session_token:$me.$storage.sessionToken}), {event_title:$me.title, parent_group_id:$me.parentID, event_type_id:$me.event_type, options: settings}).
					
			success(function(data, status, headers, config) {
				data = angular.fromJson(data);
				
				if(grades_validateAPIResponse(data)){
					
					$location.path('/event/about/'+data.event.id);
					
				}
			});
		
	};
	
	this.updateFields = function(){
		
		var url = $me.$storage.apiURL+"/event/?" + jQuery.param({filters: {event_type_id: $me.event_type, type: 'settings'}, session_token:$me.$storage.sessionToken});
		
		$http.get(url).
				
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
	
	this.updateFields();
	
}]);

grades.controller("UserInputController", ['$scope', '$sessionStorage', '$http', function($scope, $sessionStorage, $http){
	
	var $me = this;
	
	this.value = "";
	
	this.$storage = $sessionStorage;
	
	this.getUser = function(string){
		
		return $http.get($me.$storage.apiURL+"/user/?" + jQuery.param({filters:{search: string}, session_token:$me.$storage.sessionToken})).
						
			then(function(response) {
				
				for(var i=0;i<response.data.users.length;i++){
					response.data.users[i].desc = response.data.users[i].first_name + " " + response.data.users[i].last_name + " - " + response.data.users[i].mail + " (" + response.data.users[i].id + ")";
				}
				
				return response.data.users;
				
			});
	};
	
	this.selectValue = function($item, $model, $label){
	}
	
}]);

grades.controller("GroupInputController", ['$scope', '$sessionStorage', '$http', 'locale', function($scope, $sessionStorage, $http, locale){
	
	var $me = this;
	
	this.placeholder = "";
	this.inputType = "";
	
	this.$storage = $sessionStorage;
	
	this.getGroup = function(string, group_id){
		
		var filters = {search: string, items_per_page: 10, page:0};
		
		if(typeof $me.inputType !== "undefined" && $me.inputType != ""){
			filters['group_type'] = $me.inputType.split(":")[1];
		}
		
		return $http.get($me.$storage.apiURL+"/group/?" + jQuery.param({filters:filters, session_token:$me.$storage.sessionToken})).
						
			then(function(response) {
				
				for(var i=0;i<response.data.groups.length;i++){
					response.data.groups[i].desc = response.data.groups[i].name + " (" + response.data.groups[i].id + ")";
				}
				
				return response.data.groups;
				
			});
		
	}
	
	this.selectValue = function($item, $model, $label){
	}
	
	this.genPlaceholder = function(inputPlaceholder, inputTransPlaceholder){
		if(typeof inputPlaceholder !== "undefined"){
			$me.placeholder = inputPlaceholder;
		}else{
			locale.ready('common').then(function () {
                $me.placeholder = locale.getString(inputTransPlaceholder);
            });
		}
	};
	
}]);

grades.controller("EventInputController", ['$scope', '$sessionStorage', '$http', 'locale', function($scope, $sessionStorage, $http, locale){
	
	var $me = this;
	
	this.placeholder = "";
	this.$storage = $sessionStorage;
	this.inputType = "";
	
	this.getEvent = function(string){
		
		var filters = {search: string, items_per_page: 10, page:0};
		
		if($me.inputType != ""){
			filters['event_type'] = $me.inputType.split(":")[1];
		}
		
		return $http.get($me.$storage.apiURL+"/event/?" + jQuery.param({filters:filters, session_token:$me.$storage.sessionToken})).
						
			then(function(response) {
				
				for(var i=0;i<response.data.events.length;i++){
					response.data.events[i].desc = response.data.events[i].title + " (" + response.data.events[i].id + ")";
				}
				
				return response.data.events;
				
			});
	};
	
	this.genPlaceholder = function(inputPlaceholder, inputTransPlaceholder){
		if(typeof inputPlaceholder !== "undefined"){
			$me.placeholder = inputPlaceholder;
		}else{
			locale.ready('common').then(function () {
                $me.placeholder = locale.getString(inputTransPlaceholder);
            });
		}
	};
	
	this.selectValue = function($item, $model, $label){
	}
	
}]);

grades.controller("DateInputController", ['$scope', '$sessionStorage', '$http', 'locale', '$element', function($scope, $sessionStorage, $http, locale, $element){
	
	var $me = this;
	
	this.placeholder = "";
	this.$storage = $sessionStorage;
	this.inputType = "";
	$scope.useTime = false;
	this.format = "";
	
	this.genPlaceholder = function(inputPlaceholder, inputTransPlaceholder){
		if(typeof inputPlaceholder !== "undefined"){
			$me.placeholder = inputPlaceholder;
		}else{
			locale.ready('common').then(function () {
                $me.placeholder = locale.getString(inputTransPlaceholder);
            });
		}
	};
	
	this.selectValue = function($item, $model, $label){
	}
	
	this.initDatepicker = function(useTime){
		$me.useTime = useTime;
		
		jQuery($element).find(".datepicker").each(function(index){
			$me.format = (useTime == true) ? 'DD. MM. YYYY HH:mm' : 'DD. MM. YYYY';
			
			jQuery(this).datetimepicker({
				format: $me.format,
				stepping: 5,
				locale: 'de'
			});
		});
		
	};
	
}]);