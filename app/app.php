<?php

require_once __DIR__ . '/bootstrap.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\BCryptPasswordEncoder;
use Silex\Provider\SecurityServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;

$app = new Silex\Application();

require __DIR__ . '/../resources/config/dev.php';

// Register logging
$app->register(new Silex\Provider\MonologServiceProvider(), array(
    'monolog.logfile' => __DIR__ . '/../resources/log/app.log',
));

$app->register(new UrlGeneratorServiceProvider());

// Setup sessions
$app->register(new Silex\Provider\SessionServiceProvider());

// General Service Provder for Controllers
$app->register(new Silex\Provider\ServiceControllerServiceProvider());

// Register database handle
$app->register(new Silex\Provider\DoctrineServiceProvider(), $app['db.options']);

// The request body should only be parsed as JSON if the Content-Type header begins with application/json. 
$app->before(function (Request $request) {
    if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
        $data = json_decode($request->getContent(), true);
        $request->request->replace(is_array($data) ? $data : array());
    }
});

$app['security.authentication.success_handler.auth'] = $app->share(function ($app) {
    return new ChrDb\Security\AuthSuccessHandler();
});

$app['security.authentication.failure_handler.auth'] = $app->share(function ($app) {
    return new ChrDb\Security\AuthFailureHandler();
});

// Define a custom encoder for Security/Authentication
$app['security.encoder.digest'] = $app->share(function ($app) {
    // uses the password-compat encryption
    return new BCryptPasswordEncoder(10);
});

// Security definition.
$app->register(new SecurityServiceProvider(), array(
    'security.firewalls' => array(
        // Login URL is open to everybody.
        // 'login' => array(
        //      'pattern' => '^/api/login$',
        //      'anonymous' => true,
        //  ),
        // Any other URL requires auth.
        'auth' => array(
            //'pattern' => '^.*$',
            'pattern' => '^/api$',
            'form'      => array(
                'login_path'         => '/api/auth/login',
                'check_path'         => '/api/login',
                'username_parameter' => 'username',
                'password_parameter' => 'password'
            ),
            'logout'    => array('logout_path' => '/api/auth/logout'),
            'users'     => $app->share(function() use ($app) {
                return new ChrDb\Security\UserProvider($app);
            }),
        ),
    ),
));

//
// API ROUTES/CONTROLLERS
//
$app['api.gene.controller'] = $app->share(function() use ($app) {
    return new ChrDb\Api\GeneController();
});
$app->get('/api/gene/search/{term}', "api.gene.controller:searchAction");
$app->get('/api/gene/fetch/{id}', "api.gene.controller:fetchAction");

$app['api.user.controller'] = $app->share(function() use ($app) {
    return new ChrDb\Api\UserController();
});
$app->get('/api/user/{id}', "api.user.controller:fetchAction");
$app->post('/api/user/create', "api.user.controller:createAction");
$app->post('/api/user/update', "api.user.controller:updateAction");
$app->post('/api/user/login', "api.user.controller:loginAction");

$app['api.auth.controller'] = $app->share(function() use ($app) {
    return new ChrDb\Api\AuthController();
});
$app->get('/api/auth/login', "api.auth.controller:loginAction");
$app->get('/api/auth/logout', "api.auth.controller:logoutAction");

// must return $app for unit testing to work
return $app;