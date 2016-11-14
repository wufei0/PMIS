var app = angular.module('utils',[]);

app.service('utilsService',function($http) {

	this.tasks = function(scope) {
		
		switch(scope.views.task) {
			
			case "fix-leaves":
				fixLeaves();
			break;
			
		}

		function fixLeaves() {
			
			$http({
			  method: 'POST',
			  url: 'ajax.php',
			  data: {},
			  headers : {'Content-Type': 'application/x-www-form-urlencoded'}
			}).then(function mySucces(response) {
				
				
				
			}, function myError(response) {
				 
			  // error
				
			});				
			
			function fixLeavesStart() {
			
				$("#pbar").progressbar("value",10);
				scope.$apply(function() {
					scope.views.status = '';
				});
			
			}
	
		}
	
	};
	
});

app.directive('utilsTasks', function() {
	
	return {
		restrict: 'A',
		link: function(scope, element, attrs) {
		  
			element.bind('click', function() {

				scope.views.task = attrs.utilsTasks;
				
				switch (scope.views.task) {
					
					case "fix-leaves":
						
						$("#dialog_box").dialog('open');
						scope.$apply(function() {
							scope.views.title = 'Fix Leaves Debit/Credit';			
							scope.views.status = 'Ready...';
						});
						
					break;
					
				}
			 
			});

		}
	};	
	
});

app.directive('startTask',function(utilsService) {
	
	return {
		restrict: 'A',
		link: function(scope, element, attrs) {
		  
			element.bind('click', function() {

				utilsService.tasks(scope);			
			 
			});

		}
	};		
	
});

app.controller('utilsCtrl', function($scope) {
	
	$scope.views = {};
	
	$("#dialog-button").button({icons:{primary:'ui-icon-calendar'}});
	
	$("#pbar").progressbar();
	
	$(".start_button").button({icons:{primary:'ui-icon-play'}});
	
});