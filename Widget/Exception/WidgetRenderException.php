<?php
namespace Pandora3\Core\Widget\Exception;

use RuntimeException;
use Pandora3\Core\Application\Exception\CoreException;

class WidgetRenderException extends RuntimeException implements CoreException { }