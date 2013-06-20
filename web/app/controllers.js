'use strict';

/* Controllers */

function IndexCtrl($scope, GeneSrch) {
	$scope.genes = {};
	$scope.submitted = false;
  $scope.geneSelected = {};
  $scope.selected = false;

	$scope.$safeApply = function () {
       var $scope, fn, force = false;
       if (arguments.length == 1) {
           var arg = arguments[0];
           if (typeof arg == 'function') {
               fn = arg;
           }
           else {
               $scope = arg;
           }
       }
       else {
           $scope = arguments[0];
           fn = arguments[1];
           if (arguments.length == 3) {
               force = !!arguments[2];
           }
       }
       $scope = $scope || this;
       fn = fn || function () { };
       if (force || !$scope.$$phase) {
           $scope.$apply ? $scope.$apply(fn) : $scope.apply(fn);
       }
       else {
           fn();
       }
   };

  $scope.submit = function() {
	    if (this.term) {
	    	$scope.submitted = true;
	    	$scope.genes = GeneSrch.query({term: this.term});
	    	$scope.$safeApply();
	    }
	};

	$scope.loadGene = function(gene) {
		console.log(gene);
		$scope.geneSelected = gene;
		$scope.$safeApply();
	}

  $scope.geneRowClass = function(gene) {
    return gene === $scope.geneSelected ? 'info' : undefined;
  };
}

function GeneCtrl($scope, $routeParams, Gene) {
	$scope.gene = Gene.get({id: $routeParams.id});
}
