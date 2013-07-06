'use strict';

/* App Module */
angular.module('chrdb', ['chrdbServices'])
.run(function($rootScope, $location, $http, User, Config){
    $rootScope.$on('$routeChangeStart', function (event, next, current) {
        if ($location.path() !== '/user/login' && $location.path() !== '/user/create') {
            User.auth().
                success(function(response) {
                    //console.log('authed');
                    Config.resetParams();
                    $rootScope.userConfig = Config.getParams();
                    $rootScope.userConfig.showManageUserBtn = true;
                    $rootScope.userConfig.showLogoutBtn = true;
                }).
                error(function(response) {
                    //console.log('auth error');
                    $location.path('/user/login');
                });
        }
    });
})
.config(function($routeProvider, $httpProvider) {
    // Listen for 401 respondes from the API, log out user when this happens
    var logsOutUserOn401 = ['$q', '$location', function ($q, $location) {
        var success = function (response) {
          return response;
        };

        var error = function (response) {
          if (response.status === 401) {
            //redirect them back to login page
            $location.path('/user/login');
            return $q.reject(response);
          }
          else {
            return $q.reject(response);
          }
        };

        return function (promise) {
          return promise.then(success, error);
        };
    }];
    $httpProvider.responseInterceptors.push(logsOutUserOn401);
    $routeProvider.
        when('/index', {templateUrl: 'app/partials/index.html', controller: IndexCtrl}).
        //when('/login', {templateUrl: 'app/partials/login.html', controller: LoginCtrl}).
        //when('/account/create', {templateUrl: 'app/partials/account.html', controller: AccountCtrl}).
        when('/user/login', {templateUrl: 'app/partials/user.html', controller: UserCtrl}).
        //when('/user/logout', { controller: LogoutCtrl}).
        when('/user/create', {templateUrl: 'app/partials/user.html', controller: UserCtrl}).
        when('/gene/:id', {templateUrl: 'app/partials/gene.html', controller: GeneCtrl}).
        otherwise({redirectTo: '/index'});
});