<?php
namespace Pandora3\Core\Router\Exceptions;

use Throwable;
use LogicException;
use Pandora3\Core\Interfaces\Exceptions\ApplicationException;

/**
 * Class RouteNotFoundException
 * @package Pandora3\Core\Router\Exceptions
 */
class RouteNotFoundException extends LogicException implements ApplicationException {

	/**
	 * @param string $requestUri
	 * @param Throwable|null $previous
	 */
	public function __construct(string $requestUri, ?Throwable $previous = null) {
		$message = "No matched route for uri '$requestUri'";
		parent::__construct($message, E_WARNING, $previous);
	}
	
}