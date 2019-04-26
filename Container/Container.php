<?php
namespace Pandora3\Core\Container;

use Closure;
use Pandora3\Core\Container\Exception\ParameterNotResolvedException;
use Pandora3\Core\Container\Exception\ContainerException;
use Pandora3\Core\Container\Exception\ClassNotFoundException;
use Pandora3\Core\Container\Exception\DependencyNotInstantiableException;
use ReflectionClass;
use ReflectionException;
use ReflectionParameter;

/**
 * Class Container
 * @package Pandora3\Core\Container
 */
class Container {

	/** @var array $dependencies */
	protected $dependencies = [];

	/** @var array $instances */
	protected $instances = [];

	/**
	 * @param array $dependencies
	 */
	public function setDependencies(array $dependencies): void {
		foreach($dependencies as $interface => $dependency) {
			$this->set($interface, $dependency);
		}
	}
	
	/**
	 * @param array $dependencies
	 */
	public function setDependenciesShared(array $dependencies): void {
		foreach($dependencies as $interface => $dependency) {
			$this->setShared($interface, $dependency);
		}
	}

	/**
	 * @param string $abstract
	 * @param string|Closure $dependency
	 */
	public function set(string $abstract, $dependency): void {
		$this->dependencies[$abstract] = [$dependency, false];
	}
	
	/**
	 * @param string $abstract
	 * @param string|Closure $dependency
	 */
	public function setShared(string $abstract, $dependency): void {
		$this->dependencies[$abstract] = [$dependency, true];
	}
	
	/* *
	 * @param string $abstract
	 * @param mixed $instance
	 */
	/* public function setInstance(string $abstract, $instance): void {
		$this->instances[$abstract] = $instance;
	} */

	/**
	 * @param ReflectionParameter $parameter
	 * @param string $className
	 * @return mixed
	 * @throws ParameterNotResolvedException
	 */
	protected function resolvePrimitive(ReflectionParameter $parameter, string $className) {
		if (!$parameter->isDefaultValueAvailable()) {
			throw new ParameterNotResolvedException($className, $parameter->getName());
		}
		return $parameter->getDefaultValue();
	}

	/**
	 * @param string $className
	 * @param array $dependencies
	 * @param array $params
	 * @return array
	 * @throws ContainerException
	 */
	protected function resolveDependencies(string $className, array $dependencies, array $params = []): array {
		$result = [];
		foreach ($dependencies as $dependency) {
			if (array_key_exists($dependency->name, $params)) {
				$result[] = $params[$dependency->name];
				continue;
			}
			/** @var ReflectionParameter $dependency */
			$class = $dependency->getClass();
			$result[] = ($class !== null)
				? $this->resolve($class->name)
				: $this->resolvePrimitive($dependency, $className);
		}
		return $result;
	}

	/**
	 * @param string $class
	 * @param array $params
	 * @return mixed
	 * @throws ContainerException
	 */
	protected function build($class, array $params = []) {
		if ($class instanceof Closure) {
		 	return $class($this, $params);
		}

		try {
			$reflection = new ReflectionClass($class);
		} catch (ReflectionException $ex) {
			throw new ClassNotFoundException($class, $ex);
		}

		if (!$reflection->isInstantiable()) {
			throw new DependencyNotInstantiableException($class);
		}

		$constructor = $reflection->getConstructor();
		if ($constructor === null) {
			return new $class();
		}

		$dependencies = $constructor->getParameters();
		$arguments = $this->resolveDependencies($class, $dependencies, $params);

		return $reflection->newInstanceArgs($arguments);
	}

	/**
	 * @param string|Closure $abstract
	 * @param array $params
	 * @return mixed
	 * @throws ContainerException
	 */
	protected function resolve($abstract, array $params = []) {
		if (isset($this->instances[$abstract])) {
			return $this->instances[$abstract];
		}

		[$class, $shared] = $this->dependencies[$abstract] ?? [$abstract, false];

		// $object = $this->build($class, $params);

		if ($abstract === $class || $class instanceof Closure) {
			$object = $this->build($class, $params);
		} else {
			$object = $this->resolve($class, $params);
		}

		if ($shared) {
			$this->instances[$abstract] = $object;
		}
		
		return $object;
	}

	/**
	 * @param string $abstract
	 * @param array $params
	 * @return mixed
	 * @throws ContainerException
	 */
	public function get(string $abstract, array $params = []) {
		return $this->resolve($abstract, $params);
	}

}