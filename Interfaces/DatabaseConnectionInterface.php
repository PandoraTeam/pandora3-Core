<?php
namespace Pandora3\Core\Interfaces;

interface DatabaseConnectionInterface {

	function connect(): void;

	function close(): void;

}