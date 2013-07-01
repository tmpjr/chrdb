<?php

namespace ChrDb\Api;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use InvalidArgumentException;
use ChrDb\Security\Auth;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

class UserController
{
	public function createAction(Request $request, Application $app)
	{
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
	}

	public function updateAction(Request $request, Application $app)
	{
		$id = intval($request->get('id'));
		$username = trim($request->get('username'));
		$password = $request->get('password');
		$activated = intval($request->get('activated'));

		$roles = 'ROLE_ADMIN';
		$hash = password_hash($password, PASSWORD_BCRYPT);

		$sql = "UPDATE
					user
				SET
					updated_on = NOW(),
					username = :username,
					pwd_hash = :pwd_hash,
					roles = :roles,
					activated = :activated
				WHERE
					id = :id";
		$stmt = $app['db']->prepare($sql);
		$stmt->bindValue(':username', $username);
		$stmt->bindValue(':pwd_hash', $hash);
		$stmt->bindValue(':roles', $roles);
		$stmt->bindValue(':activated', $activated);
		$stmt->bindValue(':id', $id);
		$stmt->execute();

		return new JsonResponse(array(
			'success' => true,
			'user_id' => $app['db']->lastInsertId()
		));
	}

	public function loginAction(Request $request, Application $app)
	{
		$username = trim($request->get('inputEmail'));
		$password = $request->get('inputPassword');

		try {
			$auth = new Auth($app);
			$user = $auth->getAuthenticatedUser($username, $password);
		} catch (BadCredentialsException $e) {
			return new Response($e->getMessage(), 401);
		} catch (UsernameNotFoundException $e) {
			return new Response($e->getMessage(), 401);
		} catch (\Exception $e) {
			return new Response($e->getMessage(), 401);
		}

		unset($user['passwd']);
		$app['session']->set('user', $user);

		return new JsonResponse($user);
	}

	public function logoutAction(Application $app)
	{
		$app['session']->clear();
		return new Response('Logged out', 200);
	}

	public function fetchAction(Request $request, Application $app)
	{
		$id = intval($request->get('id'));

		//try {
			$stmt = $app['db']->prepare("SELECT * FROM user WHERE id = :id");
			$stmt->bindValue(':id', $id);
			$stmt->execute();
			$user = $stmt->fetch();
		//} catch (UserNotFoundException $e) {
		//	return new JsonResponse(array('message' => $e->getMessage(), 'status' => 'error'));
		//}

		$response = array(
			'status' 	=> 'success',
			'data' 		=> $user
		);

		return new JsonResponse($response);
	}
}