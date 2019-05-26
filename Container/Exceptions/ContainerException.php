<?php
namespace Pandora3\Core\Container\Exceptions;

use RuntimeException;
use Pandora3\Core\Interfaces\Exceptions\CoreException;

/**
 * Class ContainerException
 * @package Pandora3\Core\Container\Exceptions
 */
abstract class ContainerException extends RuntimeException implements CoreException { }