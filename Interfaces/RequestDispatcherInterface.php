<?php
namespace Pandora3\Core\Interfaces;

/**
 * Interface RequestDispatcherInterface
 * @package Pandora3\Core\Interfaces
 */
interface RequestDispatcherInterface {

	/**
	 * @param string $path
	 * @param array|null $arguments
	 * @return RequestHandlerInterface
	 */
	function dispatch(string $path, &$arguments): RequestHandlerInterface;

}
