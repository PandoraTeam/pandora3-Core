<?php
namespace Pandora3\Core\Http;

use Pandora3\Core\Interfaces\ResponseInterface;
use Pandora3\Libs\Cookie\Cookie;

/**
 * Class Response
 * @package Pandora3\Core\Http
 */
class Response implements ResponseInterface {

	/** @var string $content */
	protected $content;

	/** @var array $headers */
	protected $headers;
	
	/** @var int $status */
	protected $status;
	
	/** @var Cookie[] $cookies */
	protected $cookies = [];

	/**
	 * @param string $content
	 * @param int $status
	 * @param array $headers
	 */
	public function __construct(string $content, int $status = 200, array $headers = []) {
		$this->content = $content;
		$this->status = $status;
		$this->headers = $headers;
	}

	/**
	 * @param int $status
	 */
	public function setStatus(int $status): void {
		$this->status = $status;
	}

	/**
	 * @param string $header
	 * @param string $value
	 */
	public function setHeader(string $header, string $value): void {
		$this->headers[$header] = $value;
	}

	/**
	 * @param string $header
	 */
	public function removeHeader(string $header): void {
		unset($this->headers[$header]);
	}

	/**
	 * @param string $content
	 */
	public function setContent(string $content): void {
		$this->content = $content;
	}

	/**
	 * @return string
	 */
	public function getContent(): string {
		return $this->content;
	}

	/**
	 * @param Cookie $cookie
	 */
	public function setCookie(Cookie $cookie): void {
		$this->cookies[$cookie->domain][$cookie->path][$cookie->name] = $cookie;
	}

	/**
	 * @param string $name
	 * @param string $path
	 * @param null|string $domain
	 */
	public function removeCookie(string $name, string $path = '/', ?string $domain = null): void {
		unset($this->cookies[$domain][$path][$name]);
	}

	/**
	 * @param string $name
	 * @param string $path
	 * @param null|string $domain
	 * @param bool $isSecure
	 * @param bool $isHttpOnly
	 */
	public function clearCookie(
		string $name, string $path = '/', ?string $domain = null,
		bool $isSecure = false, bool $isHttpOnly = true
	): void {
		$this->setCookie(new Cookie(
			$name, null, [
				'expire' => 1,
				'path' => $path,
				'domain' => $domain,
				'isSecure' => $isSecure,
				'isHttpOnly' => $isHttpOnly,
			]
		));
	}

	/**
	 * @return array
	 */
	protected function getCookies(): array {
		$cookies = [];
		foreach ($this->cookies as $domainCookies) {
			foreach ($domainCookies as $pathCookies) {
				foreach ($pathCookies as $cookie) {
					$cookies[] = $cookie;
				}
			}
		}
		return $cookies;
	}

	/**
	 * @var array $statusText
	 */
	protected static $statusText = [
		100 => 'Continue',
		101 => 'Switching Protocols',
		102 => 'Processing',
		103 => 'Early Hints',
		200 => 'OK',
		201 => 'Created',
		202 => 'Accepted',
		203 => 'Non-Authoritative Information',
		204 => 'No Content',
		205 => 'Reset Content',
		206 => 'Partial Content',
		207 => 'Multi-Status',
		208 => 'Already Reported',
		226 => 'IM Used',
		300 => 'Multiple Choices',
		301 => 'Moved Permanently',
		302 => 'Found',
		303 => 'See Other',
		304 => 'Not Modified',
		305 => 'Use Proxy',
		307 => 'Temporary Redirect',
		308 => 'Permanent Redirect',
		400 => 'Bad Request',
		401 => 'Unauthorized',
		402 => 'Payment Required',
		403 => 'Forbidden',
		404 => 'Not Found',
		405 => 'Method Not Allowed',
		406 => 'Not Acceptable',
		407 => 'Proxy Authentication Required',
		408 => 'Request Timeout',
		409 => 'Conflict',
		410 => 'Gone',
		411 => 'Length Required',
		412 => 'Precondition Failed',
		413 => 'Payload Too Large',
		414 => 'URI Too Long',
		415 => 'Unsupported Media Type',
		416 => 'Range Not Satisfiable',
		417 => 'Expectation Failed',
		418 => 'I\'m a teapot',
		421 => 'Misdirected Request',
		422 => 'Unprocessable Entity',
		423 => 'Locked',
		424 => 'Failed Dependency',
		425 => 'Too Early',
		426 => 'Upgrade Required',
		428 => 'Precondition Required',
		429 => 'Too Many Requests',
		431 => 'Request Header Fields Too Large',
		451 => 'Unavailable For Legal Reasons',
		500 => 'Internal Server Error',
		501 => 'Not Implemented',
		502 => 'Bad Gateway',
		503 => 'Service Unavailable',
		504 => 'Gateway Timeout',
		505 => 'HTTP Version Not Supported',
		506 => 'Variant Also Negotiates',
		507 => 'Insufficient Storage',
		508 => 'Loop Detected',
		510 => 'Not Extended',
		511 => 'Network Authentication Required',
    ];

	protected function sendHeaders(): void {
		if (headers_sent()) {
			return;
		}
		
		foreach ($this->headers as $header => $value) {
			header("{$header}: {$value}", false, $this->status);
		}
		
		foreach ($this->getCookies() as $cookie) {
			header('Set-Cookie: '.$cookie, false, $this->status);
		}

		$statusText = self::$statusText[$this->status] ?? 'unknown status';
		$version = ($_SERVER['SERVER_PROTOCOL'] ?? '' !== 'HTTP/1.0') ? '1.1' : '1.0';
		header("HTTP/{$version} {$this->status} {$statusText}", true, $this->status);
	}
	
	public function send(): void {
		$this->sendHeaders();
		echo $this->content;
	}

}