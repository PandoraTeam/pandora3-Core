<?php
namespace Pandora3\Core\Container\Exceptions;

use Throwable;

/**
 * Class ParameterNotResolvedException
 * @package Pandora3\Core\Container\Exceptions
 */
class ParameterNotResolvedException extends ContainerException {

	/**
	 * @param string $className
	 * @param string $parameter
	 * @param Throwable|null $previous
	 */
	public function __construct(string $className, string $parameter, ?Throwable $previous = null) {
		$message = "Parameter '$parameter' not resolved for class [$className]";
		parent::__construct($message, E_WARNING, $previous);
	}

}