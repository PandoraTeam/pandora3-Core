<?php
namespace Pandora3\Core\Router\Exception;

use LogicException;
use Throwable;
use Pandora3\Core\Application\Exception\ApplicationException;

/**
 * Class RouteNotFoundException
 * @package Pandora3\Core\Router\Exception
 */
class RouteNotFoundException extends LogicException implements ApplicationException {

	/**
	 * @param string $requestUri
	 * @param Throwable|null $previous
	 */
	public function __construct(string $requestUri, Throwable $previous = null) {
		$message = "No matched route for uri '$requestUri'";
		parent::__construct($message, E_USER_WARNING, $previous);
	}
	
}