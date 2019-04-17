<?php
namespace Pandora3\Core\Interfaces;

/**
 * Interface RequestHandlerInterface
 * @package Pandora3\Core\Interfaces
 */
interface RequestHandlerInterface {

	function handle(RequestInterface $request, array $arguments = []): ResponseInterface;

	/* *
	 * @param string $path
	 * @param RequestInterface $request
	 * @return ResponseInterface
	 */
	// function dispatch(string $path, RequestInterface $request): ResponseInterface;

}