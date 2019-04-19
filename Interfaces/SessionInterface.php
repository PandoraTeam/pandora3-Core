<?php
namespace Pandora3\Core\Interfaces;

/**
 * Interface SessionInterface
 * @package Pandora3\Core\Interfaces
 */
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

	/**
	 * @param string $property
	 */
	public function clear(string $property): void;

}