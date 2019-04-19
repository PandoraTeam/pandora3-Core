<?php
namespace Pandora3\Core\Container\Exception;

use Throwable;

/**
 * Class DependencyNotInstantiableException
 * @package Pandora3\Core\Container\Exception
 */
class DependencyNotInstantiableException extends ContainerException {

	/**
	 * @param string $dependency
	 * @param Throwable|null $previous
	 */
	public function __construct(string $dependency, Throwable $previous = null) {
		$message = "Dependency '$dependency' it not instantiable";
		parent::__construct($message, E_USER_WARNING, $previous);
	}

}