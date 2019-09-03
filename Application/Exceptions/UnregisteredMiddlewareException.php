<?php
namespace Pandora3\Core\Application\Exceptions;

use Throwable;
use RuntimeException;
use Pandora3\Core\Interfaces\Exceptions\CoreException;

/**
 * Class UnregisteredMiddlewareException
 * @package Pandora3\Core\Application\Exceptions
 */
class UnregisteredMiddlewareException extends RuntimeException implements CoreException {

	/**
	 * @param string $middlewareName
	 * @param Throwable|null $previous
	 */
	public function __construct(string $middlewareName, ?Throwable $previous = null) {
		$message = "Unregistered middleware '$middlewareName'";
		parent::__construct($message, E_WARNING, $previous);
	}

}