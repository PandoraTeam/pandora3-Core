<?php
namespace Pandora3\Core\Registry;

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
	 * @internal
	 * @param string $property
	 * @return mixed|null
	 */
	public function __get(string $property) {
		return $this->get($property);
	}
	
	/**
	 * @internal
	 * @param string $property
	 * @return mixed|null
	 */
	public function __isset(string $property) {
		return $this->has($property);
	}
	
	/**
	 * @param string $property
	 * @return mixed|null
	 */
	public function get(string $property) {
		if (!array_key_exists($property, $this->data)) {
			return null; // or throw new UnknownPropertyException($property); ? todo:
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

}