<?php
namespace Pandora3\Core\Interfaces;

/**
 * Interface ControllerInterface
 * @package Pandora3\Core\Interfaces
 */
interface ControllerInterface {

	/**
	 * @return array
	 */
	function getRoutes(): array;

}