<?php 

namespace ChrDb\Security;

use Silex\Application;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Doctrine\DBAL\Connection;

class UserProvider implements UserProviderInterface
{
	private $app;

	public function __construct(Application $app)
	{
		$this->app = $app;
		$this->app['monolog']->addDebug('CALL ' . __METHOD__);
	}

	public function loadUserByUsername($username)
	{
		$this->app['monolog']->addDebug('CALL ' . __METHOD__);
		$this->app['monolog']->addDebug('username: ' . $username);
		$stmt = $this->app['db']->executeQuery("SELECT * FROM user WHERE username = ?", array(strtolower($username)));

		if (!$user = $stmt->fetch()) {
			throw new UsernameNotFoundException(sprintf('Username "%s" does not exist.', $username));
		}

		return new User($user['username'], $user['pwd_hash'], explode(',', $user['roles']));
	}

	public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    public function supportsClass($class)
    {
        return $class === 'Symfony\Component\Security\Core\User\User';
    }
}