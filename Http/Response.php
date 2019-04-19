<?php
namespace Pandora3\Core\Http;

use Pandora3\Core\Interfaces\ResponseInterface;

/**
 * Class Response
 * @package Pandora3\Core\Http
 */
class Response implements ResponseInterface {

	/** @var string $content */
	protected $content;

	/** @var array $headers */
	protected $headers;

	/**
	 * @param string $content
	 * @param array $headers
	 */
	public function __construct(string $content, array $headers = []) {
		$this->content = $content;
		$this->headers = $headers;
	}

	protected function sendHeaders(): void {
		foreach($this->headers as $header => $value) {
			header($header.': '.$value);
		}
	}

	public function getContent(): string {
		return $this->content;
	}

	public function send(): void {
		$this->sendHeaders();
		echo $this->content;
	}

}