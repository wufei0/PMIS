var app = angular.module('utils',[]);

app.service('utilsService',function($http) {

 	this.tasks = function(scope) {
		
		switch(scope.views.task) {
			
			case "fix-leaves":
				fixLeaves();
			break;
			
		}

		function fixLeaves() {
			
			$('#console-status').append('Collecting IDs...\n\n');
			
			$http({
			  method: 'POST',
			  data: scope.ids,
			  url: 'controllers/utils.php?r=fixLeavesStart'
			}).then(function mySucces(response) {
				
				scope.idCurrent = 0;
				scope.idCount = response.data.length - 1;
				fixLeaves(response.data);
			
			}, function myError(response) {
				 
			  // error
				
			});				
			
			function fixLeaves(EmpIDs) {
				
				$('#console-status').append('Fixing '+EmpIDs[scope.idCurrent]['EmpID']+'..');
				$http({
				  method: 'POST',
				  data: {id: EmpIDs[scope.idCurrent]['EmpID']},
				  url: 'controllers/utils.php?r=fixLeavesProcess'
				}).then(function mySucces(response) {
					
					if (response.data['status'] == 1) {
						
						var progress = Math.round((scope.idCurrent*100)/scope.idCount);						
						scope.views.status = scope.idCurrent + ' / ' + scope.idCount + ' (' + progress + '%)';
						$("#pbar").progressbar("value",progress);
						$('#console-status').append(response.data['content']);
						var psconsole = $('#console-status');
						psconsole.scrollTop(psconsole[0].scrollHeight - psconsole.height());					
						if (scope.idCurrent <= scope.idCount) {
							scope.idCurrent++;
							fixLeaves(EmpIDs);
						}
					
					}
				
				}, function myError(response) {
					 
				  // error
					
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
	
	$scope.ids = {};
	$scope.ids.start = 0;
	$scope.ids.end = 99999;
	
	$("#dialog-button").button({icons:{primary:'ui-icon-calendar'}});
	
	$("#pbar").progressbar();
	
	$(".start_button").button({icons:{primary:'ui-icon-play'}});
	
});