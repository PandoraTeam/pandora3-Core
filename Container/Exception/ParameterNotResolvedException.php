<?php
namespace Pandora3\Core\Container\Exception;

use Throwable;

/**
 * Class ParameterNotResolvedException
 * @package Pandora3\Core\Container\Exception
 */
class ParameterNotResolvedException extends ContainerException {

	/**
	 * @param string $className
	 * @param string $parameter
	 * @param Throwable|null $previous
	 */
	public function __construct(string $className, string $parameter, Throwable $previous = null) {
		$message = "Parameter '$parameter' not resolved for class [$className]";
		parent::__construct($message, E_USER_WARNING, $previous);
	}

}