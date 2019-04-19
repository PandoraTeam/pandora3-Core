<?php
namespace Pandora3\Core\Router;

use Closure;
use Pandora3\Core\Interfaces\RequestDispatcherInterface;
use Pandora3\Core\Interfaces\RequestHandlerInterface;
use Pandora3\Core\Interfaces\RouterInterface;
use Pandora3\Core\Router\Exception\RouteNotFoundException;

/**
 * Class Router
 * @package Pandora3\Core\Router
 */
class Router implements RouterInterface {
	
	/** @var array $routes */
	protected $routes;
	
	/**
	 * @param array $routes
	 */
	public function __construct(array $routes = []) {
		$this->routes = $routes;
	}
	
	/**
	 * @param string $routePath
	 * @param RequestHandlerInterface|RequestDispatcherInterface|Closure $handler
	 */
	public function add(string $routePath, $handler): void {
		if (array_key_exists($routePath, $this->routes)) {
			// warning: route already exist todo:
			return;
		}
		if ($handler instanceof Closure) {
			$handler = new RequestHandler($handler);
		} else if (!($handler instanceof RequestHandlerInterface) && !($handler instanceof RequestDispatcherInterface)) {
			// warning: 2-nd argument 'handler' must be one of: Closure, RequestHandlerInterface, RequestDispatcherInterface todo:
			return;
		}
		// $this->routes[$routePath] = $route;
		$this->routes = array_replace([$routePath => $handler], $this->routes);
	}

	/**
	 * @param string $path
	 * @param string $routePath
	 * @param string|null $subPath
	 * @param array|null $variables
	 * @return bool
	 */
	protected function matchRoute(string $path, string $routePath, &$subPath = null, &$variables = null): bool {
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
	 * @param string $path
	 * @param array|null $arguments
	 * @return RequestHandlerInterface
	 * @throws RouteNotFoundException
	 */
	public function dispatch(string $path, &$arguments = null): RequestHandlerInterface {
		$arguments = $arguments ?? [];
		// todo: request params, sort paths
		// var_dump(array_keys($this->routes));
		foreach ($this->routes as $routePath => $handler) {
			if ($this->matchRoute($path, $routePath, $subPath, $variables)) {
				$arguments = array_replace($arguments, $variables);
				if ($handler instanceof RequestDispatcherInterface) {
					return $handler->dispatch($subPath, $arguments);
				}
				return $handler;
			}
		}
		throw new RouteNotFoundException($path);
	}

}