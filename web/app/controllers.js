'use strict';

/* Controllers */
function NavCtrl($scope, $http, $location, authService) {
    $scope.logout = function() {
        $scope.userData = {};
        $scope.userLoggedIn = false;
        $http({
            url: '/api/user/logout',
            method: 'POST'
        })
        .success(function(data, status, headers, config){
            authService.logoutConfirmed();
        }).error(function(data, status, headers, config){
            authService.logoutConfirmed();
        });
    };
}

function IndexCtrl($scope, $http, $location, GeneSrch) {
	$scope.genes = {};
	$scope.submitted = false;
    $scope.geneSelected = {};
    $scope.selected = false;

    $scope.shouldBeOpen = false;

    $scope.$safeApply = function () {
        var $scope, fn, force = false;
        if (arguments.length == 1) {
            var arg = arguments[0];
            if (typeof arg == 'function') {
                fn = arg;
            } else {
                $scope = arg;
            }
        } else {
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
        } else {
            fn();
        }
    };

    $scope.submit = function() {
        if (this.term) {
            $scope.submitted = true;

            $scope.genes = GeneSrch.query({
                term: this.term
            },
            function success() {
                console.log('success');
            },
            function error(response) {
                console.log(response.data.message);
            });

            $scope.$safeApply();
        }
    };

    $scope.loadGene = function(gene) {
        $scope.geneSelected = gene;
        $scope.$safeApply();
    }

    $scope.geneRowClass = function(gene) {
        return gene === $scope.geneSelected ? 'info' : undefined;
    };
}

function AccountCtrl($scope, accountFactory) {
    $scope.emailHasError = false;
    $scope.formError = false;
    $scope.formSuccess = false;

    $scope.create = function() {
        accountFactory.create($scope.account)
        .success(function(data, status, headers, config){
            $scope.formSuccess = data;
        }).error(function(data, status, headers, config){
            $scope.formError = data;
        });
    }
}

function LoginCtrl($scope, $http, $location, authService) {
    $scope.submit = function() {
        $http({
            url: '/api/user/login',
            method: 'POST',
            data: $scope.login
        })
        .success(function(data, status, headers, config) {
            $scope.userData = data;
            authService.loginConfirmed();
        }).error(function(data, status, headers, config) {

        });
    }
}

function GeneCtrl($scope, $routeParams, Gene) {
	$scope.gene = Gene.get({id: $routeParams.id});
}
