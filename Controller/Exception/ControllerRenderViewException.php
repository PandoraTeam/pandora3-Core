<?php
namespace Pandora3\Core\Controller\Exception;

use RuntimeException;
use Pandora3\Core\Application\Exception\CoreException;

class ControllerRenderViewException extends RuntimeException implements CoreException { }