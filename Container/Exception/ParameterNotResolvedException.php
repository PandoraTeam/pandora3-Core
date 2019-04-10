<?php
namespace Pandora3\Core\Container\Exception;

use Throwable;

class ParameterNotResolvedException extends ContainerException {

	public function __construct(string $className, string $parameter, Throwable $previous = null) {
		$message = "Parameter '$parameter' not resolved for class [$className]";
		parent::__construct($message, E_USER_WARNING, $previous);
	}

}