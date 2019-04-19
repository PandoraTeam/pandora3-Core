<?php
namespace Pandora3\Core\Properties;

/**
 * Trait Getters
 * @package Pandora3\Core\Properties
 */
trait Getters {
	
	// todo: __isset
	
	/**
	 * @param string $property
	 * @return mixed|null
	 */
	public function __get(string $property) {
		$methodName = 'get'.ucfirst($property);
		if (method_exists($this, $methodName)) {
			return $this->{$methodName}();
		} else {
			return null;
			// throw new \Exception('Method or property does not exists'); todo:
		}
	}

}