'use strict';

/* App Module */

angular.module('chrdb', ['chrdbServices', 'http-auth-interceptor']).
  config(function($routeProvider) {
  $routeProvider.
      when('/index', {templateUrl: 'app/partials/index.html', controller: IndexCtrl}).
      when('/login', {templateUrl: 'app/partials/login.html', controller: LoginCtrl}).
      when('/account/create', {templateUrl: 'app/partials/account.html', controller: AccountCtrl}).
      when('/gene/:id', {templateUrl: 'app/partials/gene.html', controller: GeneCtrl}).
      otherwise({redirectTo: '/index'});
}).directive('chrdbAuth', function($rootScope, $http, $location) {
	return {
		restrict: 'C',
		link: function(scope, elem, attrs) {
			$rootScope.userLoggedIn = false;
            $rootScope.userData = {};

			$http.get('/api/user/checkauth')
			.success(function(response) {
	      		scope.$broadcast('event:auth-loginConfirmed');
	      		$rootScope.userData = response;
                $rootScope.userLoggedIn = true;
	    	})
	    	.error(function(){
	    		scope.$broadcast('event:auth-loginRequired');
	    	});

			scope.$on('event:auth-loginRequired', function() {
				$location.path("/login");
			});

			scope.$on('event:auth-loginConfirmed', function() {
                $rootScope.userLoggedIn = true;
				$location.path("/");
			});
		}
	};
});