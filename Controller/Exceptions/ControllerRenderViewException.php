<?php
namespace Pandora3\Core\Controller\Exceptions;

use RuntimeException;
use Pandora3\Core\Interfaces\Exceptions\CoreException;
use Throwable;

/**
 * Class ControllerRenderViewException
 * @package Pandora3\Core\Controller\Exceptions
 */
class ControllerRenderViewException extends RuntimeException implements CoreException {

	/**
	 * @param string $viewPath
	 * @param string $className
	 * @param null|Throwable $previous
	 */
	public function __construct(string $viewPath, string $className, ?Throwable $previous = null) {
		$message = "Rendering view '$viewPath' failed for [$className]";
		parent::__construct($message, E_WARNING, $previous);
	}

}