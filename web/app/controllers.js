'use strict';

/* Controllers */
function NavCtrl($scope, $http, $location, User, Config) {
    $scope.userConfig = Config.getParams();

    $scope.createAccount = function() {
        $location.path("/user/create");
    };

    $scope.signIn = function() {
        $location.path("/user/login");
    };

    $scope.logout = function() {
        User.logout();
        $location.path("/user/login");
    };
}

function IndexCtrl($scope, $http, $location, GeneSrch, Config) {
    //console.log('IndexCtrl');
	$scope.genes = {};
	$scope.submitted = false;
    $scope.geneSelected = {};
    $scope.selected = false;

    $scope.shouldBeOpen = false;

    $scope.submit = function() {
        if (this.term) {
            $scope.submitted = true;

            $scope.genes = GeneSrch.query({
                term: this.term
            });
        }
    };

    $scope.loadGene = function(gene) {
        $scope.geneSelected = gene;
    }

    $scope.geneRowClass = function(gene) {
        return gene === $scope.geneSelected ? 'info' : undefined;
    };
}

function UserCtrl($scope, $location, Config, User) {
    $scope.userContentTpl = 'app/partials/create.html';
    Config.resetParams();
    $scope.userConfig = Config.getParams();

    var path = $location.path();
    if (path.indexOf('login') > -1) {
        $scope.userConfig.showCreateUserBtn = true;
        //console.log($scope.userConfig);
        $scope.userContentTpl = 'app/partials/login.html';
    } else {
        $scope.userConfig.showLoginBtn = true;
    }

    $scope.formSubmitted = false;
    $scope.hasFormError = false;
    $scope.formErrorMessage = '';
    $scope.accountCreated = false;
    $scope.submit = function() {
        //console.log(this.user);
        $scope.formSubmitted = true;
        if (this.usrRegFrm.$valid === true) {
            User.save(this.user)
            .success(function(data, status, headers, config){
                $scope.accountCreated = true;
            })
            .error(function(data, status, headers, config){
                console.log('error', data);
                $scope.hasFormError = true;
                $scope.formErrorMessage = data;
            });
        }
    }

    $scope.loginFormSubmitted = false;
    $scope.login = function() {
        //console.log(this.login.inputEmail);
        $scope.loginFormSubmitted = true;
        if (this.usrLoginForm.$valid === true) {
            User.login(this.credentials)
            .success(function(data, status, headers, config){
                $scope.userData = data;
                //authService.loginConfirmed();
                $location.path("/");
            })
            .error(function(data, status, headers, config){
                console.log('error', data);
                $scope.hasFormError = true;
                $scope.formErrorMessage = data;
            });
        }
    }

    //console.log($location);
}

function GeneCtrl($scope, $routeParams, Gene) {
	$scope.gene = Gene.get({id: $routeParams.id});
}
