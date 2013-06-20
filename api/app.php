<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Silex\Provider\SecurityServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;

$app = new Silex\Application();

require  __DIR__ . '/config/dev.php';

// Register logging
$app->register(new Silex\Provider\MonologServiceProvider(), array(
    'monolog.logfile' => __DIR__ . '/app.log',
));

// Register database handle
$app->register(new Silex\Provider\DoctrineServiceProvider(), $app['db.options']);

//
// API ROUTES/CONTROLLERS
//

// POST - NEW USER
$app->post('/api/user', function(Request $request) use ($app) {
	$username = $request->get('username');
	$password = $request->get('password');
	$roles = implode(",", array('ROLE_ADMIN', 'ROLE_USER'));
	$activated = intval($request->get('activated'));
	$pwd_hash = password_hash($password, PASSWORD_BCRYPT);
	$sql = "INSERT INTO user (username,pwd_hash,roles,activated) VALUES (?,?,?,?)";
	$rowsInserted = $app['db']->executeUpdate($sql, array($username, $pwd_hash, $roles, $activated));
	$id = $app['db']->lastInsertId();
	return $app->json(array(
		'id' => $id
	));
});

// PUT - EDIT USER
$app->put('/api/user/{id}', function(Request $request) use ($app) {
	$id = $request->get('id');
	$username = $request->get('username');
	$password = $request->get('password');
	$roles = implode(",", array('ROLE_ADMIN', 'ROLE_USER'));
	$activated = intval($request->get('activated'));
	$pwd_hash = password_hash($password, PASSWORD_BCRYPT);
	$sql = "UPDATE user SET updated_on = NOW(), username = ?, pwd_hash = ?, roles = ?, activated = ? WHERE id = ?";
	$rowsInserted = $app['db']->executeUpdate($sql, array($username, $pwd_hash, $roles, $activated, $id));
	return $app->json(array(
		'id' => $id
	));
});

// GET - ALL USERS
$app->get('/api/user', function() use ($app) {
	$result = $app['db']->fetchAll("SELECT * FROM user");
	return $app->json(array('count' => count($result), 'data' => $result));
});

// GET - A USER
$app->get('/api/user/{id}', function($id) use ($app) {
	$data = $app['db']->fetchAssoc("SELECT * FROM user WHERE id = ?", array($id));
	return $app->json(array('data' => $data));
});

// Full text search by term
$app->get('/api/gene/search/{term}', function($term) use ($app) {
	$token = "%" . $term . "%";
	$results = $app['db']->fetchAll("SELECT * FROM gene WHERE symbol LIKE :term OR synonyms LIKE :term OR full_name LIKE :term", array(':term' => $token));
	return $app->json($results);
});

// get a speific gene by its PK
$app->get('/api/gene/{gene_id}', function($gene_id) use ($app) {
	$stmt = $app['db']->executeQuery('SELECT * FROM gene WHERE gene_id = ?', array($gene_id));
	$result = $stmt->fetch();
	return $app->json($result);
});

// must return $app for unit testing to work
return $app;