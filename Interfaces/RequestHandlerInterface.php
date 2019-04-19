<?php
namespace Pandora3\Core\Interfaces;

/**
 * Interface RequestHandlerInterface
 * @package Pandora3\Core\Interfaces
 */
interface RequestHandlerInterface {

	/**
	 * @param RequestInterface $request
	 * @param array $arguments
	 * @return ResponseInterface
	 */
	function handle(RequestInterface $request, array $arguments = []): ResponseInterface;

	/* *
	 * @param string $path
	 * @param RequestInterface $request
	 * @return ResponseInterface
	 */
	// function dispatch(string $path, RequestInterface $request): ResponseInterface;

}