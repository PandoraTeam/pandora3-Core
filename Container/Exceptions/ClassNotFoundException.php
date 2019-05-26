<?php
namespace Pandora3\Core\Container\Exceptions;

use Throwable;

/**
 * Class ClassNotFoundException
 * @package Pandora3\Core\Container\Exceptions
 */
class ClassNotFoundException extends ContainerException {

	/**
	 * @param string $className
	 * @param Throwable|null $previous
	 */
	public function __construct(string $className, ?Throwable $previous = null) {
		$message = "Class '$className' not found";
		parent::__construct($message, E_WARNING, $previous);
	}

}