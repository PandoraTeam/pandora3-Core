<?php
namespace Pandora3\Core\Middleware;

use Pandora3\Core\Interfaces\RequestDispatcherInterface;
use Pandora3\Core\Interfaces\RequestHandlerInterface;

/**
 * Class MiddlewareDispatcher
 * @package Pandora3\Core\Middleware
 */
class MiddlewareDispatcher implements RequestDispatcherInterface {
	
	/** @var RequestDispatcherInterface $dispatcher */
	protected $dispatcher;
	
	/** @var MiddlewareChain $chain */
	protected $chain;
	
	/**
	 * @param RequestDispatcherInterface $dispatcher
	 * @param MiddlewareChain $chain
	 */
	public function __construct(RequestDispatcherInterface $dispatcher, MiddlewareChain $chain) {
		$this->dispatcher = $dispatcher;
		$this->chain = $chain;
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function dispatch(string $path, ?array &$arguments = null): RequestHandlerInterface {
		$handler = $this->dispatcher->dispatch($path, $arguments);
		return $this->chain->wrapHandler($handler);
	}
	
}