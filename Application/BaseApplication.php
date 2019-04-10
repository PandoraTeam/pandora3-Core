<?php
namespace Pandora3\Core\Application;

use Closure;
use Pandora3\Core\Container\Exception\ContainerException;
use Pandora3\Core\Debug\Debug;
use Throwable;
use Pandora3\Core\Http\Request;
use Pandora3\Core\Router\Router;
use Pandora3\Core\Interfaces\RequestInterface;
use Pandora3\Core\Registry\Registry;
use Pandora3\Core\Container\Container;
use Pandora3\Core\Interfaces\ApplicationInterface;
use Pandora3\Core\Interfaces\RouterInterface;

/**
 * @property-read Registry $config
 * @property-read string $path
 * @property-read string $mode
 * @property-read RequestInterface $request
 * @property-read RouterInterface $router
*/
abstract class BaseApplication implements ApplicationInterface {

	public const MODE_DEV = 'dev';
	public const MODE_PROD = 'prod';
	public const MODE_TEST = 'test';

	/** @var Container $container */
	protected $container;

	/** @var array $properties */
	protected $properties = [];

	/** @var string $path */
	protected $path;

	/** @var string $mode */
	protected $mode;

	/** @var Registry $config */
	protected $config;

	public function __construct() {
		$this->path = $this->getPath();
		define('APP_PATH', $this->path);
		$this->config = new Registry($this->getConfig());
		$this->container = new Container();
		$this->dependencies($this->container);
	}

	/**
	 * @param Container $container
	 */
	protected function dependencies(Container $container): void {
		$container->set(Request::class, function() {
			$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
			$uri = preg_replace('#^[^/]#', '/$0', $uri);
			return new Request($uri);
		});
		$container->setDependencies([
			RequestInterface::class => Request::class,
			RouterInterface::class => Router::class,
		]);
		$this->setProperties([
			'config' =>  function() { return $this->config; },
			'path' =>  function() { return $this->path; },
			'mode' => function() { return $this->mode; },
			'request' => RequestInterface::class,
			'router' => RouterInterface::class,
		]);
	}
	
	/**
	 * @param array $properties
	 */
	protected function setProperties(array $properties): void {
		foreach($properties as $property => $dependency) {
			$this->setProperty($property, $dependency);
		}
	}

	/**
	 * @param string $property
	 * @param Closure|string $dependency
	 */
	protected function setProperty(string $property, $dependency): void {
		$this->properties[$property] = true;
		$this->container->setShared($property, $dependency);
	}

	/**
	 * @param string $property
	 * @return mixed|null
	 */
	public function __get(string $property) {
		if (array_key_exists($property, $this->properties)) {
			// todo: try catch ContainerException log property is null
//			echo '<pre>';
//			var_dump($this->container);
//			echo '</pre>';
		//	try {
				return $this->container->get($property);
		//	} catch (ContainerException $ex) {
		//		;
		//	}
		}
		if (!in_array($property, ['config', 'routes', 'path'])) {
			$methodName = 'get'.ucfirst($property);
			if (method_exists($this, $methodName)) {
				return $this->{$methodName}();
			}
		}
		return null;
		// throw new \Exception('Method or property does not exists'); todo:
	}
	
	public function __isset(string $property): bool {
		return array_key_exists($property, $this->properties);
	}

	protected function getConfig(): array {
		// warning: no config defined todo:
		return [];
	}

	protected function getRoutes(): array {
		// warning: no routes defined todo:
		return include("{$this->path}/routes.php");
	}

	protected function getPath(): string {
		try {
			$reflection = new \ReflectionClass(get_class($this));
			return dirname($reflection->getFileName());
		} catch (\ReflectionException $ex) { // will never occur
			Debug::logException($ex);
			return '';
		}
	}

	protected function getMode(): string {
		return $this->mode;
	}

	/**
	 * @param string $mode
	 * @return bool
	 */
	public function isMode(string $mode): bool {
		return $this->mode === $mode;
	}

	/**
	 * @param string $path
	 * @return array
	 */
	protected function loadConfig(string $path): array {
		return include($path) ?? [];
	}

	/**
	 * @param string $mode
	 */
	protected function _run(string $mode = ''): void {
		$this->mode = $mode;

		foreach($this->getRoutes() as $routePath => $handler) {
			if (is_string($handler)) {
				/* if (!ClassImplements($handler, RequestInterface::class))) { todo:
					// error
				} */
				$handler = $this->container->get($handler);
			}
			$this->router->add($routePath, $handler);
		}

		$handler = $this->router->dispatch($this->request->uri, $arguments);
		$response = $handler->handle($this->request, $arguments);
		$response->send();
	}

	/**
	 * @param string $mode
	 */
	public function run(string $mode = ''): void {
		// todo: move somewhere else
		register_shutdown_function(function() {
			$fatalErrors = E_ERROR | E_USER_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_RECOVERABLE_ERROR;
			$error = error_get_last();
			if ($error && ($error['type'] & $fatalErrors)) {
				echo '<pre>';
				var_dump($error);
				echo '</pre>';
			}
		});

		try {
			$this->_run($mode);
		} catch (Throwable $ex) {
			Debug::dumpException($ex);
		}
	}

}