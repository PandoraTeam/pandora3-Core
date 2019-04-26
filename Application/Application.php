<?php
namespace Pandora3\Core\Application;

use Pandora3\Core\Container\Container;
use Pandora3\Core\Interfaces\DatabaseConnectionInterface;
use Pandora3\Core\Interfaces\SessionInterface;
use Pandora3\Libs\Database\DatabaseConnection;
use Pandora3\Libs\Session\Session;
use Pandora3\Plugins\Authorisation\Authorisation;
use Pandora3\Plugins\Authorisation\UserProviderInterface;

/**
 * Class Application
 * @package Pandora3\Core\Application
 *
 * @property-read string $baseUri
 * @property-read DatabaseConnectionInterface $database
 * @property-read Authorisation $auth
 */
abstract class Application extends BaseApplication {

	/** @var Application $instance */
	protected static $instance;

	/**
	 * @return static
	 */
	public static function getInstance(): self {
		return self::$instance;
	}

	/**
	 * @param string $mode
	 */
	public function run(string $mode = self::MODE_DEV): void {
		self::$instance = $this;
		parent::run($mode);
	}

	public function getSecret(): string {
		return $this->config->get('secret');
	}

	/**
	 * @internal
	 * @return string
	 */
	protected function getBaseUri(): string {
		return $this->config->get('baseUri') ?? '/';
	}

	protected function getConfig(): array {
		return array_replace(
			$this->loadConfig($this->path.'/../config/config.php'),
			$this->loadConfig($this->path.'/../config/config'.ucfirst($this->mode).'.php'),
			$this->loadConfig($this->path.'/../config/local.php')
		);
	}

	/**
	 * @param Container $container
	 */
	protected function dependencies(Container $container): void {
		parent::dependencies($container);

		$container->setShared(DatabaseConnectionInterface::class, DatabaseConnection::class);
		$container->setShared(SessionInterface::class, Session::class);

		$this->setProperty('baseUri', function() { return $this->getBaseUri(); });

		if ($this->config->has('database')) {
			$container->set(DatabaseConnection::class, function() {
				return new DatabaseConnection($this->config->get('database'));
			});
			$this->setProperty('database', DatabaseConnectionInterface::class);
		}
		
		// todo: default UserProviderInterface
		$container->set(UserProviderInterface::class, function() {
			return null;
		});

		$this->setProperty('auth', Authorisation::class);
	}

}