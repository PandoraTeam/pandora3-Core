<?php
namespace Pandora3\Core\Container;

use Closure;
use Pandora3\Core\Container\Exceptions\ParameterNotResolvedException;
use Pandora3\Core\Container\Exceptions\ContainerException;
use Pandora3\Core\Container\Exceptions\ClassNotFoundException;
use Pandora3\Core\Container\Exceptions\DependencyNotInstantiableException;
use Pandora3\Core\Debug\Debug;
use ReflectionClass;
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
	
	/**
	 * @param array $dependencies
	 */
	public function setDependencies(array $dependencies): void {
		foreach ($dependencies as $interface => $dependency) {
			$this->set($interface, $dependency);
		}
	}

	/**
	 * @param array $dependencies
	 */
	public function setDependenciesShared(array $dependencies): void {
		foreach ($dependencies as $interface => $dependency) {
			$this->setShared($interface, $dependency);
		}
	}

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
	 * @param ReflectionParameter[] $dependencies
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
			$class = $dependency->getClass();
			$result[] = !is_null($class)
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
	protected function build(string $class, array $params = []) {
		if (!class_exists($class)) {
			throw new ClassNotFoundException($class);
		}

		$reflection = new ReflectionClass($class);
		if (!$reflection->isInstantiable()) {
			throw new DependencyNotInstantiableException($class);
		}

		$constructor = $reflection->getConstructor();
		if (is_null($constructor)) {
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

		if ($class instanceof Closure) {
		 	$object = $class($this, ...$params);
		} else if ($abstract === $class) {
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
	public function get(string $abstract, ...$params) {
		return $this->resolve($abstract, $params);
	}

	/**
	 * @param string $property
	 * @return bool
	 */
	public function has(string $property): bool {
		return array_key_exists($property, $this->dependencies);
	}

	/**
	 * @ignore
	 * @param string $property
	 * @return mixed
	 */
	public function __get(string $property) {
		if ($this->has($property)) {
			return $this->get($property);
		}
		$className = static::class;
		Debug::logException(new \Exception("Undefined property '$property' for [$className]", E_NOTICE));
		return null;
	}

	/**
	 * @ignore
	 * @param string $property
	 * @return bool
	 */
	public function __isset(string $property): bool {
		return !is_null($this->get($property));
	}

}