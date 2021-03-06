<?php
namespace Pandora3\Core\Interfaces;

use Closure;
use Pandora3\Core\Router\Exceptions\RouteNotFoundException;

/**
 * Interface RouterInterface
 * @package Pandora3\Core\Interfaces
 */
interface RouterInterface extends RequestDispatcherInterface {

	/**
	 * @param string $routePath
	 * @param Closure|RequestHandlerInterface|RequestDispatcherInterface|string $handler
	 */
	function add(string $routePath, $handler): void;

}