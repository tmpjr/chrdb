angular.module('chrdbServices', ['ngResource'])
	// Get a gene record by its primary key, return one result
	.factory('Gene', function($resource) {
		return $resource('/api/gene/:id', {}, {
			query: { method:'GET', id: 'id', isArray: true }
		});
	})
	// Search for a gene by keyword, can return multiple results
	.factory('GeneSrch', function($resource) {
		return $resource('/api/gene/search/:term', {}, {
			query: { method:'GET', term: 'term', isArray: true }
		});
	});