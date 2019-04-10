<?php
namespace Pandora3\Core\Interfaces;

interface RenderInterface {

	/**
	 * @param array $contextOverride
	 * @return string
	 */
	public function render(array $contextOverride = []): string;

}