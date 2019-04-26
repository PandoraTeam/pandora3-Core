<?php
namespace Pandora3\Core\Router;

use Closure;
use Pandora3\Core\Interfaces\RequestInterface;
use Pandora3\Core\Interfaces\ResponseInterface;
use Pandora3\Core\Interfaces\RequestHandlerInterface;

/**
 * Class RequestHandler
 * @package Pandora3\Core\Router
 */
class RequestHandler implements RequestHandlerInterface {

	/** @var Closure $handler */
	protected $handler;

	/**
	 * @param Closure $handler
	 */
	public function __construct(Closure $handler) {
		$this->handler = $handler;
	}

	public function handle(RequestInterface $request, array $arguments = []): ResponseInterface {
		$handler = $this->handler;
		return $handler($request, ...$arguments);
		// return is_string($response) ? new Response($response) : $response;
	}

}