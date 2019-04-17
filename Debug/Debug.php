<?php
namespace Pandora3\Core\Debug;

use Throwable;

/**
 * Class Debug
 * @package Pandora3\Core\Debug
 */
class Debug {

	/**
	 * @param Throwable $ex
	 */
	public static function dumpException(Throwable $ex) {
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

		echo '<b>'.($errorNames[$ex->getCode()] ?? 'Error').'</b>: ';
		echo '<pre style="display: inline;">'.str_replace('  ', '    ', htmlspecialchars($ex->getMessage())).'</pre>';
		echo ' in <b>'.$ex->getFile().'</b> on line <b>'.$ex->getLine().'</b><br>';

		$e = $ex->getPrevious();
		$trace = ($e != null) ? $e->getTraceAsString() : $ex->getTraceAsString();
		$subMessages = [];
		while ($e != null) {
			$subMessages[] = [
				'type' => 'exception',
				'level' => $e->getCode(),
				'message' => $e->getMessage(),
				'file' => $e->getFile(), // relativePath(...), todo:
				'line' => $e->getLine(),
			];

			echo '<pre style="display: inline;">    </pre><b>'.($errorNames[$ex->getCode()] ?? 'Error').'</b>: ';
			echo '<pre style="display: inline;">'.str_replace('  ', '    ', htmlspecialchars($e->getMessage())).'</pre>';
			echo ' in <b>'.$e->getFile().'</b> on line <b>'.$e->getLine().'</b><br>';

			$e = $e->getPrevious();
		}

		echo '<pre style="display: inline;">';
			echo $trace;
		echo '</pre><br>';
	}
	
	/**
	 * @param Throwable $ex
	 */
	public static function logException(Throwable $ex) {
		// todo: implement
	}

}