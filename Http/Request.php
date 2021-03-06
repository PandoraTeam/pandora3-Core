<?php
namespace Pandora3\Core\Http;

use Pandora3\Core\Debug\Debug;
use Pandora3\Core\Interfaces\RequestInterface;

/**
 * Class Request
 * @package Pandora3\Core\Http
 *
 * @property-read string $method
 * @property-read string $uri
 * @property-read string $refererUri
 * @property-read bool $isPost
 * @property-read string $protocol
 */
class Request implements RequestInterface {

	/**
	 * @internal
	 * @var string $uri
	 */
	protected $uri;

	/**
	 * @internal
	 * @var string $refererUri
	 */
	protected $refererUri;

	/**
	 * @internal
	 * @var string $method
	 */
	protected $method;

	/**
	 * @internal
	 * @var string $protocol
	 */
	protected $protocol;

	/** @var array $files */
	protected $files;

	/**
	 * @param string $uri
	 */
	public function __construct(string $uri) {
		$this->uri = $uri;
		$this->method = strtolower($_SERVER['REQUEST_METHOD']);
		$this->protocol = $_SERVER['SERVER_PROTOCOL'];

		$refererUri = $_SERVER['HTTP_REFERER'] ?? '';
		if ($refererUri) {
			$refererUri = parse_url($refererUri, PHP_URL_PATH);
			$refererUri = (($refererUri[0] ?? '') !== '/' ? '/' : '').$refererUri;
		}
		$this->refererUri = $refererUri;
	}

	/**
	 * @ignore
	 * @param string $property
	 * @return mixed
	 */
	public function __get(string $property) {
		$methods = [
			'method' => 'getMethod',
			'uri' => 'getUri',
			'refererUri' => 'getRefererUri',
			'isPost' => 'isPost',
			'protocol' => 'getProtocol',
		];
		$methodName = $methods[$property] ?? '';
		if ($methodName && method_exists($this, $methodName)) {
			return $this->{$methodName}();
		}
		$className = static::class;
		Debug::logException(new \Exception("Undefined property '$property' for [$className]", E_NOTICE));
		return null;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getUri(): string {
		return $this->uri;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getRefererUri(): string {
		return $this->refererUri;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getMethod(): string {
		return $this->method;
	}

	/**
	 * {@inheritdoc}
	 */
	public function isPost(): bool {
		return $this->method === 'post';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getProtocol(): string {
		return $this->protocol;
	}

	/**
	 * {@inheritdoc}
	 */
	public function isMethod(string $method): bool {
		return $this->method === $method;
	}

	/**
	 * {@inheritdoc}
	 */
	public function all(?string $method = null): array {
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
	 * {@inheritdoc}
	 */
	public function get(string $name) {
		return $_GET[$name] ?? null;
	}

	/**
	 * {@inheritdoc}
	 */
	public function post(string $name) {
		return $_POST[$name] ?? null;
	}

	/**
	 * {@inheritdoc}
	 */
	public function file(string $name) {
		return $_FILES[$name] ?? null;
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function getCookie(string $name) {
		return $_COOKIE[$name] ?? null;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getFiles(): array {
		if (is_null($this->files)) {
			$this->files = $_FILES; // todo: normalize files
		}
		return $this->files;
	}

}