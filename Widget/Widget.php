<?php
namespace Pandora3\Core\Widget;

use Pandora3\Core\Interfaces\RenderInterface;
use Pandora3\Core\Widget\Exception\WidgetRenderException;
use Pandora3\Plugins\Twig\TwigRenderer;

/**
 * Class Widget
 * @package Pandora3\Core\Widget
 */
abstract class Widget implements RenderInterface {
	
	/** @var array $context */
	protected $context;
	
	/**
	 * @param array $context
	 */
	public function __construct($context = []) {
		$this->context = $context;
	}
	
	abstract protected function getView(): string;
	
	/**
	 * @param array $context
	 * @return string
	 * @throws WidgetRenderException
	 */
	public function render(array $context = []): string {
		$renderer = new TwigRenderer(APP_PATH.'/Views');
		$viewPath = $this->getView();
		$context = array_replace($this->context, $context);
		try {
			return $renderer->render($viewPath, $context);
		} catch (\Throwable $ex) {
			$className = get_class($this);
			throw new WidgetRenderException("Rendering view '$viewPath' failed for [$className]", E_WARNING, $ex);
		}
	}

}