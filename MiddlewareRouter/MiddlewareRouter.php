<?php
namespace Pandora3\Core\MiddlewareRouter;

use Closure;
use Pandora3\Core\Application\Exceptions\UnregisteredMiddlewareException;
use Pandora3\Core\Debug\Debug;
use Pandora3\Core\Interfaces\RequestDispatcherInterface;
use Pandora3\Core\Interfaces\RequestHandlerInterface;
use Pandora3\Core\Middleware\Interfaces\MiddlewareInterface;
use Pandora3\Core\Middleware\MiddlewareChain;
use Pandora3\Core\Middleware\MiddlewareDispatcher;
use Pandora3\Core\Router\Router;

class MiddlewareRouter extends Router {

	/**
	 * @param string $routePath
	 * @param Closure|RequestDispatcherInterface|RequestHandlerInterface|string $handler
	 * @param array $middlewares
	 */
	public function add(string $routePath, $handler, $middlewares = []): void {
		if (array_key_exists($routePath, $this->routes)) {
			Debug::logException(new \Exception("Route handler for '$routePath' is already set", E_WARNING));
			return;
		}
		
		$handler = $this->prepareHandler($handler, $routePath);
		if ($middlewares) {
			$handler = $this->wrapHandler($handler, $middlewares);
		}
		
		// $this->routes[$routePath] = $route;
		$this->routes = array_replace([$routePath => $handler], $this->routes);
	}

	/**
	 * @param MiddlewareInterface|string[] $middlewares
	 * @return MiddlewareChain
	 */
	protected function chainMiddlewares(...$middlewares) {
		foreach ($middlewares as &$middleware) {
			if (is_string($middleware)) {
				$middleware = $this->container->get("middleware.$middleware");
				if (is_null($middleware)) {
					throw new UnregisteredMiddlewareException($middleware);
				}
			}
			if (!($middleware instanceof MiddlewareInterface)) {
				// todo: WrongMiddlewareTypeException
				throw new \LogicException("Middleware must be string or implement [MiddlewareInterface]");
			}
		}
		return new MiddlewareChain(...$middlewares);
	}
	
	/**
	 * @param RequestHandlerInterface|RequestDispatcherInterface $handler
	 * @param MiddlewareInterface|string[] $middlewares
	 * @return RequestHandlerInterface|RequestDispatcherInterface
	 */
	public function wrapHandler($handler, array $middlewares) {
		$chain = $this->chainMiddlewares(...$middlewares);
		if ($handler instanceof RequestDispatcherInterface) {
			return new MiddlewareDispatcher($handler, $chain);
		}
		return $chain->wrapHandler($handler);
	}

}