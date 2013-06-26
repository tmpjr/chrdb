<?php

require_once __DIR__ . '/bootstrap.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\BCryptPasswordEncoder;
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

// The request body should only be parsed as JSON if the Content-Type header begins with application/json. 
$app->before(function (Request $request) {
    if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
        $data = json_decode($request->getContent(), true);
        $request->request->replace(is_array($data) ? $data : array());
    }
});

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

$app->post('/api/account/create', function(Request $request) use ($app){
	$app['monolog']->addDebug('email: ' . $request->get('inputEmail'));

	$username = trim($request->get('inputEmail'));
	$password = $request->get('inputPassword');
	$passwordAgain = $request->get('inputPasswordRepeat');
	$activated = intval(0);

	if (empty($username)) {
		return new Response('Usename is a required field.', 400);
	}

	if (empty($password)) {
		return new Response('Password is a required field.', 400);
	}

	if ($password !== $passwordAgain) {
		return new Response('Passwords do not match.', 400);
	}

	$roles = 'ROLE_USER';
	$hash = password_hash($password, PASSWORD_BCRYPT);

	$stmt = $app['db']->prepare("INSERT INTO user 
		(username,pwd_hash,roles,activated) 
		VALUES (:username,:pwd_hash,:roles,:activated)");
	$stmt->bindValue(':username', $username);
	$stmt->bindValue(':pwd_hash', $hash);
	$stmt->bindValue(':roles', $roles);
	$stmt->bindValue(':activated', $activated);
	try {
		$stmt->execute();
	} catch (Exception $e) {
		$app['monolog']->addError($e->getMessage());
		return new Response('Account could no be created. User already exists.', 500);
	}

	return new Response('Account successfully created. Please search your email inbox for activation instructions.', 200);
});

// must return $app for unit testing to work
return $app;