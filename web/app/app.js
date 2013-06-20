'use strict';

/* App Module */

angular.module('chrdb', ['chrdbServices']).
  config(['$routeProvider', function($routeProvider) {
  $routeProvider.
      when('/index', {templateUrl: 'app/partials/index.html', controller: IndexCtrl}).
      when('/gene/:id', {templateUrl: 'app/partials/gene.html', controller: GeneCtrl}).
      otherwise({redirectTo: '/index'});
}]);
