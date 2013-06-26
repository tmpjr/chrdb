'use strict';

/* App Module */

angular.module('chrdb', ['chrdbServices']).
  config(function($routeProvider) {
  $routeProvider.
      when('/index', {templateUrl: 'app/partials/index.html', controller: IndexCtrl}).
      when('/login', {templateUrl: 'app/partials/login.html', controller: IndexCtrl}).
      when('/account/create', {templateUrl: 'app/partials/account.html', controller: AccountCtrl}).
      when('/gene/:id', {templateUrl: 'app/partials/gene.html', controller: GeneCtrl}).
      otherwise({redirectTo: '/index'});
});
