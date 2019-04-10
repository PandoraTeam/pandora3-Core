<?php
namespace Pandora3\Core\Interfaces;

interface ResponseInterface {

	function send(): void;

	function getContent(): string;

}