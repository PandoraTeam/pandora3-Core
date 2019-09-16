<?php
namespace Pandora3\Core\Interfaces;
use Pandora3\Libs\Cookie\Cookie;

/**
 * Interface ResponseInterface
 * @package Pandora3\Core\Interfaces
 */
interface ResponseInterface {

	function send(): void;

	function getContent(): string;

	function setStatus(int $status): void;

	function setHeader(string $header, string $value): void;

	function removeHeader(string $header): void;

	function setContent(string $content): void;

	function setCookie(Cookie $cookie): void;

	function removeCookie(string $name, string $path = '/', ?string $domain = null): void;

	function clearCookie(
		string $name, string $path = '/', ?string $domain = null,
		bool $isSecure = false, bool $isHttpOnly = true
	): void;

}