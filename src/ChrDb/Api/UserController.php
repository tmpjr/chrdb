<?php

namespace ChrDb\Api;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use InvalidArgumentException;
use ChrDb\Security\Auth;
use ChrDb\Exception;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Doctrine\DBAL\DBALException;

class UserController
{
	public function saveAction(Request $request, Application $app)
	{
		$app['monolog']->addDebug('email: ' . $request->get('inputEmail'));

        $fullName = trim($request->get('inputFullName'));
        $intendedUse = trim($request->get('inputIntendedUse'));
        $username = trim($request->get('inputEmail'));
        $password = $request->get('inputPassword');
        $passwordAgain = $request->get('inputPasswordConfirm');
        $activated = intval(0);

        if (empty($username)) {
            throw new Exception("Email is required");
        }

        if (empty($password)) {
            throw new Exception("Password is required");
        }

        if (empty($fullName)) {
            throw new Exception("Full name is required");
        }

        if ($password !== $passwordAgain) {
            throw new Exception("Passwords do not match");
        }

        $roles = 'ROLE_USER';
        $passwd = $app['security.encoder.digest']->encodePassword($password, null);

        try {
            $stmt = $app['db']->prepare("INSERT INTO user
                (username,full_name,intended_use,passwd,roles,activated)
                VALUES (:username,:full_name,:intended_use,:passwd,:roles,:activated)");
            $stmt->bindValue(':username', $username);
            $stmt->bindValue(':full_name', $fullName);
            $stmt->bindValue(':intended_use', $intendedUse);
            $stmt->bindValue(':passwd', $passwd);
            $stmt->bindValue(':roles', $roles);
            $stmt->bindValue(':activated', $activated);
            $stmt->execute();
        } catch (DBALException $e) {
            $app['monolog']->addError('Database exception' . $e->getMessage());
            throw new Exception("Database error creating acount. The authorities have been notified, you may rest easy.");
        }

		return new Response('Account successfully created', 200);
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
		return new Response('Logged out', 401);
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