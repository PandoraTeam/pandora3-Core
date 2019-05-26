<?php
namespace Pandora3\Core\Debug;

use Throwable;

/**
 * Class Debug
 * @package Pandora3\Core\Debug
 */
class Debug {

	/**
	 * @param int|string $code
	 * @return string
	 */
	public static function getErrorName($code): string {
		$errorNames = [
			E_ERROR => 'Error',
			E_WARNING => 'Warning',
			E_PARSE => 'Parse error',
			E_NOTICE => 'Notice',
			E_CORE_ERROR => 'Error',
			E_CORE_WARNING => 'Warning',
			E_USER_ERROR => 'Error',
			E_COMPILE_ERROR => 'Error',
			E_COMPILE_WARNING => 'Warning',
			E_USER_WARNING => 'Warning',
			E_USER_NOTICE => 'Notice',
			E_STRICT => 'Strict',
			E_RECOVERABLE_ERROR => 'Error',
			E_DEPRECATED => 'Deprecated',
			E_USER_DEPRECATED => 'Deprecated',
		];
		return $errorNames[$code] ?? 'Error '.$code;
	}

	/**
	 * @param Throwable $exception
	 */
	public static function dumpException(Throwable $exception) {
		echo '<b>'.self::getErrorName($exception->getCode()).'</b>: ';
		echo '<pre style="display: inline;">'.str_replace('  ', '    ', htmlspecialchars($exception->getMessage())).'</pre>';
		echo ' in <b>'.$exception->getFile().'</b> on line <b>'.$exception->getLine().'</b><br>';

		$ex = $exception->getPrevious();
		$trace = ($ex != null) ? $ex->getTraceAsString() : $exception->getTraceAsString();
		// $subMessages = [];
		
		while ($ex != null) {
			/* $subMessages[] = [
				'type' => 'exception',
				'level' => $ex->getCode(),
				'message' => $ex->getMessage(),
				'file' => $ex->getFile(), // relativePath(...), todo:
				'line' => $ex->getLine(),
			]; */

			echo '<pre style="display: inline;">    </pre><b>'.($errorNames[$ex->getCode()] ?? 'Error').'</b>: ';
			echo '<pre style="display: inline;">'.str_replace('  ', '    ', htmlspecialchars($ex->getMessage())).'</pre>';
			echo ' in <b>'.$ex->getFile().'</b> on line <b>'.$ex->getLine().'</b><br>';

			$ex = $ex->getPrevious();
		}

		echo '<pre style="display: inline;">';
			echo $trace;
		echo '</pre><br>';
	}
	
	/**
	 * @param Throwable $ex
	 */
	public static function logException(Throwable $ex) {
		\Dump::logException($ex);
	}

}