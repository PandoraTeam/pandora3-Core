<?php
namespace Pandora3\Core\Interfaces;

use Closure;

/**
 * Interface RouterInterface
 * @package Pandora3\Core\Interfaces
 */
interface RouterInterface extends RequestDispatcherInterface {

	/**
	 * @param string $routePath
	 * @param RequestHandlerInterface|RequestDispatcherInterface|Closure $handler
	 */
	function add(string $routePath, $handler): void;

}