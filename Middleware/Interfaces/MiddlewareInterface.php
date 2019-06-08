<?php
namespace Pandora3\Core\Middleware\Interfaces;

use Pandora3\Core\Interfaces\RequestHandlerInterface;
use Pandora3\Core\Interfaces\RequestInterface;
use Pandora3\Core\Interfaces\ResponseInterface;

interface MiddlewareInterface {
	
	/**
	 * @param RequestInterface $request
	 * @param array $arguments
	 * @param RequestHandlerInterface $handler
	 * @return ResponseInterface
	 */
	function process(RequestInterface $request, array $arguments, RequestHandlerInterface $handler): ResponseInterface;

}