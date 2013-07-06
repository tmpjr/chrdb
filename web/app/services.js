angular.module('chrdbServices', ['ngResource'])
	.factory('Config', function($resource) {
		var params = {
			showLoginBtn: true,
			showCreateUserBtn: false,
			showManageUserBtn: false,
			showLogoutBtn: false
		};
		return {
			getParams: function() {
				return params;
			},
			resetParams: function() {
				params.showLoginBtn = false;
				params.showCreateUserBtn = false;
				params.showManageUserBtn = false;
				params.showLogoutBtn = false;
			}
		}
	})
	// Get a gene record by its primary key, return one result
	.factory('Gene', function($resource) {
		return $resource('/api/gene/:id', {}, {
			query: { method:'GET', id: 'id', isArray: true }
		});
	})
	.factory('User', function($http) {
		return {
            auth: function() {
                return $http({
                    url: '/api/user/auth',
                    method: 'GET'
                });
            },
			save: function(user) {
				//console.log(account);
				return $http({
					url: '/api/user/save',
					method: 'POST',
					data: user
				});
				//return $http.post('/api/account/create', account);
			},
            login: function(credentials) {
                //console.log(credentials);
                return $http({
                    url: '/api/user/login',
                    method: 'POST',
                    data: credentials
                });
            },
            logout: function() {
                return $http.get('/api/user/logout');
            }
		};
	})
	// Search for a gene by keyword, can return multiple results
	.factory('GeneSrch', function($resource) {
		return $resource('/api/gene/search/:term', {}, {
			query: { method:'GET', term: 'term', isArray: true }
		});
	});