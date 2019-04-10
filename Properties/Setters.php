<?php
namespace Pandora3\Core\Properties;

trait Setters {
	
	/**
	 * @param string $property
	 * @param mixed $value
	 */
	public function __set(string $property, $value) {
		$methodName = 'set'.ucfirst($property);
		if (method_exists($this, $methodName)) {
			$this->{$methodName}($value);
		} else {
			return;
			// throw new \Exception('Private or protected properties are not accessible'); todo:
		}
	}

}