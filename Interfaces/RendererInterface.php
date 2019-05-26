<?php
namespace Pandora3\Core\Interfaces;

interface RendererInterface {

	// todo: RenderFailedException
	/**
	 * @param string $viewPath
	 * @param array $context
	 * @return string
	 * @throws \RuntimeException
	 */
	public function render(string $viewPath, array $context = []): string;

}