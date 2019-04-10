<?php
namespace Pandora3\Core\Interfaces;

interface SessionInterface {

	/**
	 * @return mixed
	 */
	public function getId();

	/**
	 * @param string $property
	 * @return mixed
	 */
	public function get(string $property);

	/**
	 * @param string $property
	 * @param mixed $value
	 */
	public function set(string $property, $value): void;

	public function clear(string $property): void;

}