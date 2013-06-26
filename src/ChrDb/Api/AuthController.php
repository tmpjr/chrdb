<?php

namespace ChrDb\Api;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use InvalidArgumentException;

class AuthController
{
	public function loginAction(Request $request, Application $app)
	{
		// return new JsonResponse(array(
		// 	'error'         => $app['security.last_error']($request),
	 //        'last_username' => $app['session']->get('_security.last_username'),
		// ));
		return new Response('Logged in', 200);	
	}

	public function logoutAction(Request $request, Application $app)
	{
		$app['session']->clear();
		return new Response('Successfully logged out', 200);
	}
}