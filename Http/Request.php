<?php
namespace Pandora3\Core\Http;

use Pandora3\Core\Interfaces\RequestInterface;

/**
 * Class Request
 * @package Pandora3\Core\Http
 *
 * @property-read string $method
 * @property-read string $uri
 * @property-read bool $isPost
 */
class Request implements RequestInterface {

	/**
	 * @internal
	 * @var string $uri
	 */
	protected $uri;
	
	/**
	 * @internal
	 * @var string $method
	 */
	protected $method;

	/** @var array $files */
	protected $files;
	
	/**
	 * @param string $uri
	 */
	public function __construct(string $uri) {
		$this->uri = $uri;
		$this->method = strtolower($_SERVER['REQUEST_METHOD']);
	}

	/**
	 * @internal
	 * @param string $property
	 * @return mixed
	 */
	public function __get(string $property) {
		$methods = [
			'method' => 'getMethod',
			'uri' => 'getUri',
			'isPost' => 'isPost',
		];
		$methodName = $methods[$property] ?? '';
		if ($methodName && method_exists($this, $methodName)) {
			return $this->{$methodName}();
		} else {
			return null;
			// throw new \Exception('Method or property does not exists'); todo:
		}
	}

	/**
	 * @return string
	 */
	public function getUri(): string {
		return $this->uri;
	}
	
	/**
	 * @return string
	 */
	public function getMethod(): string {
		return $this->method;
	}
	
	/**
	 * @return bool
	 */
	public function isPost(): bool {
		return $this->method === 'post';
	}

	/**
	 * @param string $method
	 * @return bool
	 */
	public function isMethod(string $method): bool {
		return $this->method === $method;
	}

	/**
	 * @param string|null $method
	 * @return array
	 */
	public function all($method = null): array {
		switch ($method) {
			case 'get':
				return $_GET;
			case 'post':
				return array_replace($_POST, $this->getFiles());
			default:
				return array_replace($_GET, $_POST, $this->getFiles());
		}
	}

	/**
	 * @param string $param
	 * @return mixed
	 */
	public function get(string $param) {
		return $_GET[$param] ?? null;
	}

	/**
	 * @param string $param
	 * @return mixed
	 */
	public function post(string $param) {
		return $_POST[$param] ?? null;
	}

	/**
	 * @param string $param
	 * @return mixed
	 */
	public function file(string $param) {
		return $_FILES[$param] ?? null;
	}

	/**
	 * @return array
	 */
	public function getFiles(): array {
		if ($this->files === null) {
			$this->files = $_FILES; // todo: normalize files
		}
		return $this->files;
	}

}