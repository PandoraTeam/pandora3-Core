<?php
namespace Pandora3\Core\Middleware;

use Pandora3\Core\Interfaces\RequestHandlerInterface;
use Pandora3\Core\Interfaces\RequestInterface;
use Pandora3\Core\Interfaces\ResponseInterface;
use Pandora3\Core\Middleware\Interfaces\MiddlewareInterface;

/**
 * Class MiddlewareHandler
 * @package Pandora3\Core\Middleware
 */
class MiddlewareHandler implements RequestHandlerInterface {

	/** @var MiddlewareInterface $middleware */
	protected $middleware;
	
	/** @var RequestHandlerInterface $handler */
	protected $handler;

	public function __construct(MiddlewareInterface $middleware, RequestHandlerInterface $handler) {
		$this->middleware = $middleware;
		$this->handler = $handler;
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function handle(RequestInterface $request, array $arguments = []): ResponseInterface {
		return $this->middleware->process($request, $arguments, $this->handler);
	}
	
}