<?php 

namespace ChrDb\Security;

use Silex\Application;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

class Auth
{
	public function __construct(Application $app)
	{
		$this->app = $app;
	}

	public function getAuthenticatedUser($username, $password)
	{
		$stmt = $this->app['db']->executeQuery("SELECT * FROM user WHERE username = ?", array(strtolower($username)));

		if (!$user = $stmt->fetch()) {
			throw new UsernameNotFoundException(sprintf('Username "%s" does not exist.', $username));
		}

		if (!$this->app['security.encoder.digest']->isPasswordValid($user['passwd'], $password, null)) {
			throw new BadCredentialsException("Invalid credentials provided.");
		}

		return $user;
	}
}