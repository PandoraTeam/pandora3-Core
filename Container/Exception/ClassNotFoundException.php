<?php
namespace Pandora3\Core\Container\Exception;

use Throwable;

/**
 * Class ClassNotFoundException
 * @package Pandora3\Core\Container\Exception
 */
class ClassNotFoundException extends ContainerException {

	public function __construct(string $className, Throwable $previous = null) {
		$message = "Class [$className] not found";
		parent::__construct($message, E_USER_WARNING, $previous);
	}

}