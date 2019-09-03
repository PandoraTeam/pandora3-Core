<?php
namespace Pandora3\Core\Router;

use Closure;
use Pandora3\Core\Container\Container;
use Pandora3\Core\Controller\Controller;
use Pandora3\Core\Debug\Debug;
use Pandora3\Core\Interfaces\RequestDispatcherInterface;
use Pandora3\Core\Interfaces\RequestHandlerInterface;
use Pandora3\Core\Interfaces\RouterInterface;
use Pandora3\Core\Router\Exceptions\RouteNotFoundException;

/**
 * Class Router
 * @package Pandora3\Core\Router
 */
class Router implements RouterInterface {
	
	/** @var array $routes */
	protected $routes;
	
	/** @var Container $container */
	protected $container;
	
	/**
	 * @param array $routes
	 * @param Container $container
	 */
	public function __construct(array $routes = [], Container $container = null) {
		$this->routes = $routes;
		$this->container = $container ?? new Container;
	}
	
	// todo: get post methods
	/**
	 * @param string $routePath
	 * @param Closure|RequestDispatcherInterface|RequestHandlerInterface|string $handler
	 */
	public function add(string $routePath, $handler): void {
		if (array_key_exists($routePath, $this->routes)) {
			Debug::logException(new \Exception("Route handler for '$routePath' is already set", E_WARNING));
			return;
		}
		
		$handler = $this->prepareHandler($handler, $routePath);
		
		// $this->routes[$routePath] = $route;
		$this->routes = array_replace([$routePath => $handler], $this->routes);
	}
	
	/**
	 * @param Closure|RequestHandlerInterface|RequestDispatcherInterface|string $handler
	 * @param string $routePath
	 * @return RequestHandlerInterface|RequestDispatcherInterface
	 */
	// todo: remove $routePath to compose exception message outside
	protected function prepareHandler($handler, $routePath) {
		if ($handler instanceof Closure) {
			$handler = new RequestHandler($handler);
		} else {
			if (is_string($handler)) {
				$handler = $this->container->get($handler);
			}
			if (!(
				$handler instanceof RequestHandlerInterface ||
				$handler instanceof RequestDispatcherInterface
			)) {
				// todo: exception class
				throw new \LogicException("Route handler for '$routePath' must be [Closure] or implement [RequestHandlerInterface] or [RequestDispatcherInterface]");
			}
			if ($handler instanceof Controller) {
				$handler->setApplication($this->container->get('app'));
			}
		}
		
		return $handler;
	}

	/**
	 * @param string $path
	 * @param string $routePath
	 * @param string|null $subPath
	 * @param array|null $variables
	 * @return bool
	 */
	protected function matchRoute(string $path, string $routePath, ?string &$subPath = null, ?array &$variables = null): bool {
		$pattern = preg_replace('#\{[^\}/]+\}#', '([^/]+)', $routePath);

		$ending = '$';
		if (preg_match('#^(.*)/\*$#', $pattern, $matches)) {
			$pattern = $matches[1];
			$ending = '(?:$|/)';
		}

		$pattern = '#^'.str_replace(['-', '*'], ['\-', '.+'], $pattern).$ending.'#';
		$matched = preg_match($pattern, $path, $matches);
		if ($matched) {
			$matchedPath = array_shift($matches);
			$variables = $matches;
			$subPath = '/'.substr($path, strlen($matchedPath));
		}
		return $matched;
	}

	/**
	 * {@inheritdoc}
	 */
	public function dispatch(string $path, ?array &$arguments = null): RequestHandlerInterface {
		$arguments = $arguments ?? [];
		// todo: request params, sort paths
		// var_dump(array_keys($this->routes));
		foreach ($this->routes as $routePath => $handler) {
			if ($this->matchRoute($path, $routePath, $subPath, $variables)) {
				$arguments = array_replace($arguments, $variables);
				if ($handler instanceof RequestDispatcherInterface) {
					$handler = $handler->dispatch($subPath, $arguments);
				}
				return $handler;
			}
		}
		throw new RouteNotFoundException($path);
	}

}