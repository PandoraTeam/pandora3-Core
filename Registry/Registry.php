<?php
namespace Pandora3\Core\Registry;

use Pandora3\Core\Debug\Debug;

/**
 * Class Registry
 * @package Pandora3\Core\Registry
 */
class Registry {

	/** @var array $data */
	protected $data;

	/**
	 * @param array $data
	 */
	public function __construct(array $data = []) {
		$this->data = $data;
	}

	/**
	 * @param string $property
	 * @return mixed|null
	 */
	public function get(string $property) {
		if (!array_key_exists($property, $this->data)) {
			return null;
		}
		return $this->data[$property];
	}
	
	/**
	 * @param string $property
	 * @return bool
	 */
	public function has(string $property): bool {
		return array_key_exists($property, $this->data);
	}

	/**
	 * @ignore
	 * @param string $property
	 * @return mixed|null
	 */
	public function __get(string $property) {
		if ($this->has($property)) {
			return $this->get($property);
		}
		$className = static::class;
		Debug::logException(new \Exception("Undefined property '$property' for [$className]", E_NOTICE));
		return null;
	}

	/**
	 * @ignore
	 * @param string $property
	 * @return bool
	 */
	public function __isset(string $property): bool {
		return !is_null($this->get($property));
	}

}