<?php
namespace Pandora3\Core\Middleware;

use Pandora3\Core\Interfaces\RequestHandlerInterface;
use Pandora3\Core\Middleware\Interfaces\MiddlewareInterface;

/**
 * Class MiddlewareChain
 * @package Pandora3\Core\Middleware
 */
class MiddlewareChain {
	
	/** @var MiddlewareInterface[] $middlewares */
	protected $middlewares;
	
	/**
	 * @param MiddlewareInterface[] $middlewares
	 */
	public function __construct(...$middlewares) {
		$this->middlewares = $middlewares;
	}
	
	/**
	 * @param RequestHandlerInterface $handler
	 * @return RequestHandlerInterface
	 */
	public function wrapHandler(RequestHandlerInterface $handler): RequestHandlerInterface {
		foreach (array_reverse($this->middlewares) as $middleware) {
			$handler = new MiddlewareHandler($middleware, $handler);
		}
		return $handler;
	}
	
}