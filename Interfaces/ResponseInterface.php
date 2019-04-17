<?php
namespace Pandora3\Core\Interfaces;

/**
 * Interface ResponseInterface
 * @package Pandora3\Core\Interfaces
 */
interface ResponseInterface {

	function send(): void;

	function getContent(): string;

}