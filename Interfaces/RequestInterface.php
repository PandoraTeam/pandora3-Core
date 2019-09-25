<?php
namespace Pandora3\Core\Interfaces;

/**
 * Interface RequestInterface
 * @package Pandora3\Core\Interfaces
 *
 * @property-read string $uri
 * @property-read string $refererUri
 * @property-read bool $isPost
 */
interface RequestInterface {
	
	/**
	 * @return string
	 */
	function getUri(): string;

	/**
	 * @return string
	 */
	function getRefererUri(): string;
	
	/**
	 * @return string
	 */
	function getMethod(): string;

	/**
	 * @return bool
	 */
	function isPost(): bool;

	/**
	 * @param string $method
	 * @return bool
	 */
	function isMethod(string $method): bool;

	/**
	 * @param string|null $method
	 * @return array
	 */
	function all(?string $method): array;
	
	/**
	 * @param string $param
	 * @return mixed
	 */
	function get(string $param);
	
	/**
	 * @param string $param
	 * @return mixed
	 */
	function post(string $param);
	
	/**
	 * @param string $param
	 * @return mixed
	 */
	function file(string $param);
	
	/**
	 * @param string $name
	 * @return mixed
	 */
	public function getCookie(string $name);
	
	/**
	 * @return array
	 */
	function getFiles(): array;

}