<?php
namespace Pandora3\Core\Router;

use Closure;
use Pandora3\Core\Http\Response;
use Pandora3\Core\Interfaces\RequestInterface;
use Pandora3\Core\Interfaces\ResponseInterface;
use Pandora3\Core\Interfaces\RouteInterface;

class Route implements RouteInterface {

	/** @var Closure $handler */
	protected $handler;

	/**
	 * @param Closure $handler
	 */
	public function __construct(Closure $handler) {
		$this->handler = $handler;
	}

	/**
	 * @param string $path
	 * @param RequestInterface $request
	 * @return ResponseInterface
	 */
	public function dispatch(string $path, RequestInterface $request): ResponseInterface {
		$handler = $this->handler;
		$response = $handler($request);
		return is_string($response) ? new Response($response) : $response;
	}

}