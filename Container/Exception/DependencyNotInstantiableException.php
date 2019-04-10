<?php
namespace Pandora3\Core\Container\Exception;

use Throwable;

class DependencyNotInstantiableException extends ContainerException {

	public function __construct(string $dependency, Throwable $previous = null) {
		$message = "Dependency '$dependency' it not instantiable";
		parent::__construct($message, E_USER_WARNING, $previous);
	}

}