
"use strict";

/* Global Storage */

if(typeof(Storage) === "undefined"){
	alert("Your browser is too old! Upgrade it!");
	throw new Error("Browser version too old to use this piece of software! Consider updating.");
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
	
	this.$storage.apiURL			= 'http://grades.dev/api/v1';
	this.$storage.modalInstance		= {
										close: function(){
											return;
										}
									}
	
	this.user = $me.$storage.user;
	
	$scope.$on('$routeChangeSuccess', function(next, current) { 
		
		if(typeof $me.$storage.modalInstance !== "undefined" && $me.$storage.modalInstance !== null && typeof $me.$storage.modalInstance.close !== "undefined"){
			$me.$storage.modalInstance.close();
		}
		
	});
	
	if(typeof this.$storage.user === "undefined"){
		this.$storage.user = {};
	}
	
	if(typeof this.$storage.activeNavigation === "undefined"){
		this.$storage.activeNavigation = 0;
	}
	
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

appController.directive("groupOverview", function(){
	return {
		restrict: 'E',
		scope: {
			groupType: '=groupType',
			groupTypeID: '=groupTypeId',
			parentGroupID: '=parentGroupId',
			groupButtons: '=groupButtons'
	    },
		templateUrl: '/res/html/directives/groupOverview.html'
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
                    
                    if(jQuery(".calendar").length > 0){
	                    
	                    if(jQuery(element).attr("data-time") == "false"){
		                    jQuery(element).val(jQuery(".calendar").fullCalendar('getDate').format("DD. MM. YYYY"));
	                    }else{
		                    var minute = moment().minute();
		                    
							jQuery(element).val(jQuery(".calendar").fullCalendar('getDate').minute(5 * Math.round( minute / 5 )).format("DD. MM. YYYY HH:mm"));
		                    
	                    }
	                       
					}
                    
                });
            });
        },
    };
});

appController.filter('toTrusted', ['$sce', function($sce){
    return function(text) {
        return $sce.trustAsHtml(text);
    };
}]);

appController.filter('toGender', ['$sce', function($sce){
    return function(text) {
	    var gender;
	    switch(text){
		    case 0:
		    default:
		    	gender = 'male';
		    	break;
		    case 1:
		    	gender = 'female';
		    	break;
	    }
        return gender;
    };
}]);

appController.filter('toDate', ['$sce', function($sce){
    return function(text) {
        return moment.unix(text).format("DD. MM. YYYY");
    };
}]);

appController.filter('toShortDate', ['$sce', function($sce){
    return function(text) {
        return moment.unix(text).format("DD. MM");
    };
}]);

appController.filter('toTime', ['$sce', function($sce){
    return function(text) {
        return moment.unix(text).format("HH:mm");
    };
}]);

appController.filter('toDateTime', ['$sce', function($sce){
    return function(text) {
        return moment.unix(text).format("DD. MM. YYYY HH:mm");
    };
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
						
						$http.get($me.$storage.apiURL+"/user/me/?" + jQuery.param({session_token:$me.$storage.sessionToken})).
			
							success(function(data, status, headers, config) {
								
								data = angular.fromJson(data);
								
								if(grades_validateAPIResponse(data)){
									
									$me.$storage.user = data.user;
									
									$location.path('/dashboard');
									$me.$storage.activeNavigation = 1;
									
								}
							});
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
    this.event_modifiers = [];
    
    this.eventSource = [];
    
    this.getRepeatingEvents = function(start, end, timezone, callback) {
	    
	    var events = [];
	    
	    var diff, e, range, d;
	    
	    for(var i=0;i < $me.repeating_events.length;i++){
		    
		    e = $me.repeating_events[i];
		    
		    diff = start.weeks() - e.start.weeks();
		    
		    e.start.add(diff, "weeks");
		    e.end.add(diff, "weeks");

			range = Math.max(end.weeks() - start.weeks(), 1);
			
			for(var j=0;j<range;j++){
				var e_ = jQuery.extend(true, {}, e);
				e_.start = e.start.clone();
				e_.end = e.end.clone();
				
				for(var k=0;k<e_.modifiers.length;k++){
					d = moment.unix(e_.modifiers[k].date);
					if((e_.start.isSame(d,'day')) || (e_.end.isSame(d,'day'))){ // doesn't work if a lesson is longer than 2 days which is unrealistic
						e_.className = e_.className.concat(e_.modifiers[k].classes);
					}
				}
				
				events.push(e_);
				
				e.start.add(1,"weeks");
				e.end.add(1,"weeks");
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
							editable:	data.events[i].can_edit,
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
							
							event.modifiers				= [];
							for(var j=0;j<$me.event_modifiers.length;j++){
								if($me.event_modifiers[j].lesson_id == data.events[i].id){
									event.modifiers.push($me.event_modifiers[j]);
									$me.event_modifiers.slice(j,1);
								}
							}
							
							$me.repeating_events.push(event);
						}else if(data.events[i].event_type == 'test' || data.events[i].event_type == 'lesson'){
							
							var applied = false;
							var mod = {
									classes:['event-type-'+data.events[i].event_type],
									lesson_id: data.events[i].options.lesson_id,
									date: data.events[i].options.date
							};
							
							for(var j=0;j<$me.repeating_events.length;j++){
								if($me.repeating_events[j].id == data.events[i].lesson_id){
									$me.repeating_events[j].modifiers.push(mod);
									applied = true;
								}
							}
							
							if(!applied){
								$me.event_modifiers.push(mod);
							}
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
			$me.$storage.modalInstance.close();
			$me.$storage.modalInstance = $modal.open({
				animation: true,
				controller: 'ModalController',
				templateUrl: '/res/html/event/event.html',
				size: 'lg',
				resolve: {
					modalParams: function(){
						
						return {
							event: calEvent
						};
						
					}
				}
			});
			
		},
		dayClick: function(date, jsEvent, view){
			
			if(view.name == "agendaDay"){
				$me.$storage.modalInstance.close();
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


grades.controller("GroupOverviewController", ['$scope', '$http' ,'$sessionStorage', '$location', '$routeParams', '$modal', function($scope, $http, $sessionStorage, $location, $routeParams, $modal){
	
	var $me = this;
	
	this.$storage = $sessionStorage;
	
	$scope.groupRowsDynamic = [];
	$scope.groupRowsStatic = [];
	
	$scope.groupRows = [];
	
	$scope.displayedGroupRows = $scope.groupRows;
	
	this.groupButtons = $scope.$parent.groupButtons;
	
	$scope.$parent.$watch('groupButtons', function(){
		$me.btns = $scope.$parent.groupButtons;
	});
	
	this.page = 0;
	this.items_per_page = 30;
	
	$scope.$watch('groupRowsDynamic', function(){
		$scope.groupRows = [].concat($scope.groupRowsDynamic, $scope.groupRowsStatic);
	});
	
	$scope.$watch('groupRowsStatic', function(){
		
		$scope.groupRows = [].concat($scope.groupRowsDynamic, $scope.groupRowsStatic);
	});
	
	$scope.$parent.$watch('groupType', function(){
		$me.updateGroups();
	});
	$scope.$parent.$watch('groupTypeID', function(){
		$me.updateGroups();
	});
	$scope.$parent.$watch('parentGroupID', function(){
		$me.updateGroups();
	});
	
	this.updateGroups = function(){
		
		var filters = {items_per_page: $me.items_per_page, page:$me.page, member_of:true};
		
		if((typeof $scope.$parent.groupType !== "undefined") && $scope.$parent.groupType !== ""){
			filters['group_type'] = $scope.$parent.groupType;
		}
		
		if((typeof $scope.$parent.groupTypeID !== "undefined") && $scope.$parent.groupTypeID != -1 && $scope.$parent.groupTypeID != "-" && $scope.$parent.groupTypeID != "" && (!isNaN($scope.$parent.parentGroupID))){
			filters['group_type_id'] = $scope.$parent.groupTypeID;
		}
		
		$http.get($me.$storage.apiURL+"/group/?" + jQuery.param({filters:filters,session_token:$me.$storage.sessionToken})).
				
				success(function(data, status, headers, config) {
					
					data = angular.fromJson(data);
					
					if(grades_validateAPIResponse(data)){
						
						for(var i=0;i<data.groups.length;i++){
							data.groups[i].classes=['static'];
						}
						$scope.groupRowsStatic = data.groups;
					}
				});
				
		delete filters.member_of;
		
		filters.not_member_of = true;
		
		if((typeof $scope.$parent.parentGroupID !== "undefined") && $scope.$parent.parentGroupID != -1 && $scope.$parent.parentGroupID != "-" && $scope.$parent.parentGroupID != "" && (!isNaN($scope.$parent.parentGroupID))){
			filters['parent_group_id'] = $scope.$parent.parentGroupID;
			
			
			$http.get($me.$storage.apiURL+"/group/?" + jQuery.param({filters:filters,session_token:$me.$storage.sessionToken})).
				
				success(function(data, status, headers, config) {
					
					data = angular.fromJson(data);
					
					if(grades_validateAPIResponse(data)){
						
						for(var i=0;i<data.groups.length;i++){
							data.groups[i].classes=[];
						}
						
						$scope.groupRowsDynamic = data.groups;
						
					}
				});
			
		}
		
	};
	
	this.about = function(group_id){
		$me.$storage.modalInstance.close();
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
	
}]);


grades.controller("GroupEditController", ['$scope', '$http' ,'$sessionStorage', '$location', '$routeParams', '$timeout', function($scope, $http, $sessionStorage, $location, $routeParams, $timeout){
	
	var $me = this;
	
	this.$storage = $sessionStorage;
	
	$me.settings = [];
	
	this.group_id = -1;
	
	if(typeof $routeParams.groupID !== "undefined" && (! isNaN($routeParams.groupID))){
		
		this.group_id = $routeParams.groupID;
		
	}else if(typeof $scope.$parent.modalParams.group_id !== "undefined"){
		
		this.group_id = $scope.$parent.modalParams.group_id;
		
	}
	
	this.fillData = function(){
		
		$scope.$evalAsync(function(){
			
			jQuery("[name="+$me.settings[$me.settings.length -1].key+"]").waitUntilExists(function(){
				
				var field;
				
				for(var i=0;i<$me.settings.length;i++){
					field = jQuery("[name="+$me.settings[i].key+"]");
					if(field.is("input[type='checkbox']")){
						field.prop('checked', ($me.settings[i].value == true));
					}else if(field.is(".datepicker")){
						field.val(moment.unix($me.settings[i].value).format("DD. MM. YYYY"));
					}else{
						field.val($me.settings[i].value);
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
			}else if(jQuery(this).is(".datepicker")){
				if(jQuery(this).val()!=""){
					settings[field_name] = moment(jQuery(this).val(), "DD. MM. YYYY HH:mm").unix();
				}
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
		
		$http.get($me.$storage.apiURL+"/group/"+$me.group_id+"/settings/?" + jQuery.param({session_token:$me.$storage.sessionToken})).
				
			success(function(data, status, headers, config) {
				
				data = angular.fromJson(data);
				
				if(grades_validateAPIResponse(data)){
					
					$me.settings = data.settings;
					
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

grades.controller("EventController", ['$scope', '$http' ,'$sessionStorage', '$routeParams', '$location', '$modal', function($scope, $http, $sessionStorage, $routeParams, $location, $modal){
	
	var $me = this;
	
	this.$scope				= $scope;
	this.eventType 			= "";
	this.title				= "";
	this.options			= {};
	this.canEdit			= false;
	
	this.start				= null;
	this.end				= null;
	
	this.events				= [];
	
	this.created = false;
	
	this.eventID = -1;
		
	if(typeof $routeParams.eventID !== "undefined" && (! isNaN($routeParams.eventID))){
		
		this.eventID = $routeParams.eventID;
		
	}else if(typeof $scope.$parent.modalParams.event !== "undefined"){
		
		this.eventID = $scope.$parent.modalParams.event.id;
		
		this.start = $scope.$parent.modalParams.event.start;
		this.end = $scope.$parent.modalParams.event.end;
		
	}
	
	this.$storage = $sessionStorage;
	
	this.loadData = function(){
			
		$http.get($me.$storage.apiURL+"/event/" + $me.eventID + "/?" + jQuery.param({session_token:$me.$storage.sessionToken})).
					
			success(function(data, status, headers, config) {
				
				data = angular.fromJson(data);
				
				if(grades_validateAPIResponse(data)){
					
					$me.title				= data.event.title;
					$me.eventType			= data.event.event_type;
					$me.options				= data.event.options;
					$me.canEdit				= data.event.can_edit;
					
					$me.events				= data.event.events;
					
				}
			});
		
		
	}
	
	this.editEvent = function(id){
		
		if(typeof id === "undefined"){
			id = $me.eventID;
		}
		$me.$storage.modalInstance.close();		
		$me.$storage.modalInstance = $modal.open({
			animation: true,
			controller: 'ModalController',
			templateUrl: '/res/html/event/edit.html',
			size: 'lg',
			resolve: {
				modalParams: function(){
					
					return {
						event: {
							id:id
						}
					};
					
				}
			}
		});
		
	};
	
	this.calendarIs = function(date){
		
		var d = moment.unix(date);
		
		return d.isSame($me.start,'day') || d.isSame($me.end,'day');
	};
	
	this.addGrade = function($event){
		
		var formElement = angular.element($event.target);
		
		var test_id = jQuery(formElement).find("[name='test_id']").val();
		var grade = jQuery(formElement).find("[name='grade']").val();
		var grade_id = jQuery(formElement).find("[name='grade_id']").val();
		
		if(typeof test_id !== "undefined" && test_id !== null && test_id != "" && !isNaN(test_id)){
			
			if(typeof grade !== "undefined" && grade !== null && grade != "" && !isNaN(grade)){
				
				if(grade_id == -1 && $me.created == false){
					
					$http.post($me.$storage.apiURL+"/grade/?" + jQuery.param({session_token:$me.$storage.sessionToken}), {event_id:test_id, grade:grade}).
							
						success(function(data, status, headers, config) {
							data = angular.fromJson(data);
							
							if(grades_validateAPIResponse(data)){
								$me.created = true;
							}
						});
					
				}else{
					
					$http.put($me.$storage.apiURL+"/grade/"+grade_id+"/settings/?" + jQuery.param({session_token:$me.$storage.sessionToken}), {option_key:'grade',value:grade}).
							
						success(function(data, status, headers, config) {
							data = angular.fromJson(data);
							
							if(grades_validateAPIResponse(data)){
								//do something
							}
						});
					
				}
				
			}
			
		}
		
	};
	
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

grades.controller("EventEditController", ['$scope', '$http' ,'$sessionStorage', '$location', '$routeParams', function($scope, $http, $sessionStorage, $location, $routeParams){
	
	var $me = this;
	
	this.title = "";
	$me.settings = [];
	this.eventID = -1;
	
	if(typeof $routeParams.eventID !== "undefined" && (! isNaN($routeParams.eventID))){
		
		this.eventID = $routeParams.eventID;
		
	}else if(typeof $scope.$parent.modalParams.event !== "undefined"){
		
		this.eventID = $scope.$parent.modalParams.event.id;
		
	}
	
	this.$storage = $sessionStorage;
	
	
	this.update = function($event){
		
		var formElement = angular.element($event.target);
		var settings = {};
		
		jQuery(formElement).find("[name]").each(function(index){
			
			var field_name = jQuery(this).attr("name").trim();
			
			if(jQuery(this).is("input[type='checkbox']")){
				settings[field_name] = jQuery(this).is(":checked");
			}else if(jQuery(this).is(".datepicker")){
				if(jQuery(this).val()!=""){
					settings[field_name] = moment(jQuery(this).val(), "DD. MM. YYYY HH:mm").unix();
				}
			}else{
				settings[field_name] = jQuery(this).val();
			}
		});
		
		for (var key in settings) {

			if (settings.hasOwnProperty(key)) {
		          
				$http.put($me.$storage.apiURL+"/event/"+$me.eventID+"/settings/?" + jQuery.param({session_token:$me.$storage.sessionToken}), {option_key:key, value:settings[key]}).
			
					success(function(data, status, headers, config) {
						
						data = angular.fromJson(data);
						
						if(grades_validateAPIResponse(data)){
							
							return true;
	
						}
					});
		    }
		}
		
	};
	
	this.fillData = function(){
		
		$scope.$evalAsync(function(){
			
			jQuery("[name="+$me.settings[$me.settings.length -1].key+"]").waitUntilExists(function(){
				
				var field;
				
				for(var i=0;i<$me.settings.length;i++){
					field = jQuery("[name="+$me.settings[i].key+"]");
					if(field.is("input[type='checkbox']")){
						field.prop('checked', ($me.settings[i].value == true));
					}else if(field.is(".datepicker")){
						field.val(moment.unix($me.settings[i].value).format("DD. MM. YYYY"));
					}else{
						field.val($me.settings[i].value);
					}
				}
				
			});
		});
	};
	
	this.repeatDone = function(){
		$me.fillData();
	};
	
	this.updateFields = function(){
		
		var url = $me.$storage.apiURL+"/event/"+$me.eventID+"/settings/?" + jQuery.param({session_token:$me.$storage.sessionToken});
		
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

grades.controller("DashboardAverageGradeController", ['$scope', '$sessionStorage', '$http', '$location', function($scope, $sessionStorage, $http, $location){
	
	var $me = this;
	
	this.$storage = $sessionStorage;
	
	this.average = "";
	this.points = "";
	
	this.loadData = function(){
		
		$http.get($me.$storage.apiURL+"/grade/?" + jQuery.param({session_token:$me.$storage.sessionToken})).
					
			success(function(data, status, headers, config) {
				
				data = angular.fromJson(data);
				
				if(grades_validateAPIResponse(data)){
					$me.average = data.average;
					$me.points = data.points;
				}
			});
		
	};
	
	this.loadData();
	
}]);

grades.controller("DashboardFutureTestsController", ['$scope', '$sessionStorage', '$http', '$location', function($scope, $sessionStorage, $http, $location){
	
	var $me = this;
	
	this.$storage = $sessionStorage;
	
	this.tests = [];
	
	this.loadData = function(){
		
		$http.get($me.$storage.apiURL+"/event/?" + jQuery.param({filters:{future_only:true,event_type:'test',order_by_date:'ASC'},session_token:$me.$storage.sessionToken})).
					
			success(function(data, status, headers, config) {
				
				data = angular.fromJson(data);
				
				if(grades_validateAPIResponse(data)){
					$me.tests = data.events;
				}
			});
		
	};
	
	this.loadData();
	
}]);

grades.controller("DashboardTaskController", ['$scope', '$sessionStorage', '$http', '$location', function($scope, $sessionStorage, $http, $location){
	
	var $me = this;
	
	this.$storage = $sessionStorage;
	
	this.tasks = [];
	
	this.loadData = function(){
		
		$http.get($me.$storage.apiURL+"/event/?" + jQuery.param({filters:{future_only:true,event_type:'task',order_by_date:'ASC'},session_token:$me.$storage.sessionToken})).
					
			success(function(data, status, headers, config) {
				
				data = angular.fromJson(data);
				
				if(grades_validateAPIResponse(data)){
					$me.tasks = data.events;
				}
			});
		
	};
	
	this.loadData();
	
}]);

grades.controller("ProfileController", ['$scope', '$sessionStorage', '$http', '$location', '$element', function($scope, $sessionStorage, $http, $location, $element){
	
	var $me = this;
	
	this.placeholder = "";
	this.$storage = $sessionStorage;
	
	this.subjects = [];
	
	this.user = $me.$storage.user;
	this.parentID = -1;
	
	this.reloadData = function(){
		
		$http.get($me.$storage.apiURL+"/group/?" + jQuery.param({filters: {group_type_id: 3, member_of:true}, session_token:$me.$storage.sessionToken})).
					
			success(function(data, status, headers, config) {
				
				data = angular.fromJson(data);
				
				if(grades_validateAPIResponse(data)){
					$me.subjects = data.groups;
				}
			});
		
	};
	
	this.userProperty = function(key){
		
		return (typeof $me.user[key] !== "undefined" ? $me.user[key] : '');
		
	};
	
	this.edit = function(){
		$location.path('/profile/settings');
	};
	
	$me.reloadData();
	
}]);

grades.controller("ProfileEditController", ['$scope', '$sessionStorage', '$http', '$location', '$modal', function($scope, $sessionStorage, $http, $location, $modal){
	
	var $me = this;
	
	this.placeholder = "";
	this.$storage = $sessionStorage;
	this.user = $me.$storage.user;
	this.settings = {};
	this.parentID = "";
	
	this.btns = [
		{
			click: function(group){
				$scope.$parent.$parent.join_group(group.id, $me.$storage.user.id, 1, function(){
					group.can_join = false;
					group.can_leave = true;
				});
			},
			condition: function(group){
				return group.can_join;
			},
			transKey:'join'
		},
		{
			click: function(group){
				$scope.$parent.$parent.leave_group(group.id, $me.$storage.user.id, 1, function(){
					group.can_join = true;
					group.can_leave = false;
				});
			},
			condition: function(group){
				return group.can_leave;
			},
			transKey:'leave'
		},
		{
			click: function(group){
				$me.$storage.modalInstance.close();
				$me.$storage.modalInstance = $modal.open({
					animation: true,
					controller: 'ModalController',
					templateUrl: '/res/html/group/edit.html',
					size: 'lg',
					resolve: {
						modalParams: function(){
							
							return {
								group_id: group.id
							};
							
						}
					}
				});
			},
			condition: function(group){
				return group.can_edit;
			},
			transKey:'edit'
		}
	];
	
	this.userProperty = function(key){
		
		return (typeof $me.user[key] !== "undefined" ? $me.user[key] : '');
		
	};
	
	this.update = function($event){
		
		var formElement = angular.element($event.target);
		var settings = {};
		
		jQuery(formElement).find("[name]").each(function(index){
			
			var field_name = jQuery(this).attr("name").trim();
			
			if(field_name != ""){
				
				if(jQuery(this).is("input[type='checkbox']")){
					settings[field_name] = jQuery(this).is(":checked");
				}else if(jQuery(this).is(".datepicker")){
					if(jQuery(this).val()!=""){
						settings[field_name] = moment(jQuery(this).val(), "DD. MM. YYYY HH:mm").unix();
					}
				}else{
					settings[field_name] = jQuery(this).val();
				}
				
			}
			
		});
		
		for (var key in settings) {

			if (settings.hasOwnProperty(key)) {
		          
				$http.put($me.$storage.apiURL+"/user/me/settings/?" + jQuery.param({session_token:$me.$storage.sessionToken}), {option_key:key, value:settings[key]}).
			
					success(function(data, status, headers, config) {
						
						data = angular.fromJson(data);
						
						if(grades_validateAPIResponse(data)){
							
							return true;
	
						}
					});
		    }
		}
		
		$http.get($me.$storage.apiURL+"/user/me/?" + jQuery.param({session_token:$me.$storage.sessionToken})).
			
			success(function(data, status, headers, config) {
				
				data = angular.fromJson(data);
				
				if(grades_validateAPIResponse(data)){
					
					$me.$storage.user = data.user;
					
					$location.path('/profile/me');
					
				}
			});
	};
	
	this.getFields = function(){
		
		$http.get($me.$storage.apiURL+"/user/me/settings/?" + jQuery.param({filters:{fields:false},session_token:$me.$storage.sessionToken})).
					
				success(function(data, status, headers, config) {
					
					data = angular.fromJson(data);
					
					if(grades_validateAPIResponse(data)){
						
						$me.settings = data.settings;
						
					}
				});
		
	};
	
	this.fillData = function(){
		
		$scope.$evalAsync(function(){
			
			jQuery("[name="+$me.settings[$me.settings.length -1].key+"]").waitUntilExists(function(){
				
				var field;
				
				for(var i=0;i<$me.settings.length;i++){
					field = jQuery("[name="+$me.settings[i].key+"]");
					if(field.is("input[type='checkbox']")){
						field.prop('checked', ($me.settings[i].value == true));
					}else if(field.is(".datepicker")){
						field.val(moment.unix($me.settings[i].value).format("DD. MM. YYYY"));
					}else{
						field.val($me.settings[i].value);
					}
				}
				
			});
		});
	};
	
	this.repeatDone = function(){
		$me.fillData();
	};
	
	this.editGroup = function(){
		$me.$storage.modalInstance.close();
		$me.$storage.modalInstance = $modal.open({
			animation: true,
			controller: 'ModalController',
			templateUrl: '/res/html/group/edit.html',
			size: 'lg',
			resolve: {
				modalParams: function(){
					
					return {
						group_id: $me.parentID
					};
					
				}
			}
		});
	};
	
	this.isEditable = function(){
		return $me.parentID != "" && !isNaN($me.parentID) && $me.parentID > 0;
	};
	
	this.addGroup = function(){
		$me.$storage.modalInstance.close();
		$me.$storage.modalInstance = $modal.open({
			animation: true,
			controller: 'ModalController',
			templateUrl: '/res/html/group/add.html',
			size: 'lg',
			resolve: {
				modalParams: function(){
					
					return {
						
					};
					
				}
			}
		});
	};
	
	
	this.getFields();
	
	
}]);