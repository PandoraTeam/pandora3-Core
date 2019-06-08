<?php
namespace Pandora3\Core\Interfaces;

use Pandora3\Core\Router\Exceptions\RouteNotFoundException;

/**
 * Interface RequestDispatcherInterface
 * @package Pandora3\Core\Interfaces
 */
interface RequestDispatcherInterface {

	/**
	 * @param string $path
	 * @param array|null $arguments
	 * @return RequestHandlerInterface
	 * @throws RouteNotFoundException
	 */
	function dispatch(string $path, ?array &$arguments): RequestHandlerInterface;

}
