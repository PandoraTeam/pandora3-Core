<?php
namespace Pandora3\Core\Properties;
use Pandora3\Core\Debug\Debug;

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
		}
		$className = static::class;
		Debug::logException(new \Exception("Undefined property '$property' for [$className]", E_NOTICE));
		return null;
	}

}