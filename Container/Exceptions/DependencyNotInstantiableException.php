<?php
namespace Pandora3\Core\Container\Exceptions;

use Throwable;

/**
 * Class DependencyNotInstantiableException
 * @package Pandora3\Core\Container\Exceptions
 */
class DependencyNotInstantiableException extends ContainerException {

	/**
	 * @param string $dependency
	 * @param Throwable|null $previous
	 */
	public function __construct(string $dependency, ?Throwable $previous = null) {
		$message = "Dependency '$dependency' it not instantiable";
		parent::__construct($message, E_WARNING, $previous);
	}

}