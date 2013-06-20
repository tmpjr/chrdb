<?php

require_once __DIR__ . '/vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\BCryptPasswordEncoder;

use Silex\Provider\SecurityServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;

$app = new Silex\Application();

require __DIR__ . '/config/dev.php';

// Register logging
$app->register(new Silex\Provider\MonologServiceProvider(), array(
    'monolog.logfile' => __DIR__ . '/app.log',
));

// Register Twig
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__ . '/views',
));

// Register database handle
$app->register(new Silex\Provider\DoctrineServiceProvider(), $app['db.options']);

$app->match('/', function() use ($app) {
	$name = "Tom Ploskina";
	return $app['twig']->render('viewport.html', array(
        'name' => $name,
    ));
});

$app->get('/gene', function() use ($app){
	$result = $app['db']->fetchAll("SELECT * FROM gene LIMIT 100");
	return $app->json(array('count' => count($result), 'data' => $result));
});

$app->get('/gene/{symbol}', function($symbol) use ($app){
	$stmt = $app['db']->executeQuery('SELECT * FROM gene WHERE symbol = ?', array($symbol));
	$result = $stmt->fetch();
	return $app->json(array('data' => $result));
});

// must return $app for unit testing to work
return $app;