<?php
namespace Pandora3\Core\Controller;

// temporary
use App\Widgets\Menu\Menu;

use Closure;
use Pandora3\Core\Controller\Exceptions\ControllerRenderViewException;
use Pandora3\Core\Debug\Debug;
use Pandora3\Core\Interfaces\RendererInterface;
use Pandora3\Core\Middleware\Interfaces\MiddlewareInterface;
use Pandora3\Core\MiddlewareRouter\MiddlewareRouter;
use Pandora3\Libs\Application\Application; // todo: fix dependency
use Pandora3\Core\Interfaces\RequestDispatcherInterface;
use Pandora3\Core\Interfaces\RequestHandlerInterface;
use Pandora3\Core\Container\Container;
use Pandora3\Core\Interfaces\ControllerInterface;
use Pandora3\Core\Interfaces\RequestInterface;
use Pandora3\Core\Interfaces\ResponseInterface;
use Pandora3\Core\Interfaces\RouterInterface;
use Pandora3\Core\Router\RequestHandler;
use Pandora3\Core\Http\Response;
use Pandora3\Plugins\Twig\TwigRenderer; // todo: extend dependency from application container

/**
 * Class Controller
 * @package Pandora3\Core\Controller
 *
 * @property-read string $baseUri
 * @property-read Application $app
 */
abstract class Controller implements ControllerInterface, RequestDispatcherInterface {

	/** @var Container $container */
	protected $container;

	/** @var Application $app */
	protected $app;
	
	/** @var bool $initialised */
	private $initialised;

	/**
	 * @internal
	 * @var string $_baseUri
	 */
	protected $_baseUri;

	/** @var string $name */
	protected $name;

	/** @var string $layout */
	protected $layout = 'Layout/Main.twig'; // todo: extract somewhere

	/** @var RequestInterface $request */
	protected $request;
	
	/** @var RequestDispatcherInterface $dispatcher */
	protected $dispatcher;

	protected function init(): void {
		if ($this->initialised) {
			return;
		}
		$this->name = $this->getName();
		$this->container = new Container;
		$this->dependencies($this->container);
		$this->initialised = true;
	}
	
	/**
	 * @param Container $container
	 */
	protected function dependencies(Container $container): void {
		$container->setDependencies([
			RouterInterface::class => MiddlewareRouter::class,
			RendererInterface::class => TwigRenderer::class,
		]);

		$container->set(MiddlewareRouter::class, function(Container $c, $routes = []) {
			return new MiddlewareRouter($routes, $this->app->container);
		});
		$container->setShared(TwigRenderer::class, function() {
			$renderer = new TwigRenderer(APP_PATH.'/Views');
			// todo: extract to extension
			$renderer->addFunctions([
				'dump' => 'dump',
				'debugOutput' => function() {
					$output = \Dump::getOutput();
					return $output ? '<div class="debug-output">'.$output.'</div>' : '';
				},
				'assets' => Closure::fromCallable([$this, 'getAssets']),
			]);
			return $renderer;
		});
	}
	
	/**
	 * @param RouterInterface|MiddlewareRouter $router
	 * @param array $routes
	 */
	protected function registerRoutes($router, array $routes) {
		foreach($routes as $routePath => $handler) {
			$middlewares = [];
			if (is_array($handler)) {
				[$middlewares, $handler] = $handler;
				if (!is_array($middlewares)) {
					$middlewares = [$middlewares];
				}
			}
			if (is_string($handler)) {
				$handler = $this->getActionHandler($handler);
			}
			
			if ($router instanceof MiddlewareRouter) {
				$router->add($routePath, $handler, $middlewares);
			} else {
				$router->add($routePath, $handler);
			}
		}
	}

	/**
	 * @return RequestDispatcherInterface
	 */
	protected function getDispatcher(): RequestDispatcherInterface {
		if (is_null($this->dispatcher)) {
			/** @var RouterInterface|MiddlewareRouter $dispatcher */
			$dispatcher = $this->container->get(RouterInterface::class);
			$routes = $this->getRoutes();
			$this->registerRoutes($dispatcher, $routes);
			
			if ($dispatcher instanceof MiddlewareRouter) {
				$middlewares = $this->getMiddlewares();
				if ($middlewares) {
					$dispatcher = $dispatcher->wrapHandler($dispatcher, $middlewares);
				}
			}
			$this->dispatcher = $dispatcher;
		}
		return $this->dispatcher;
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function getRoutes(): array {
		return [];
	}
	
	/**
	 * @return MiddlewareInterface[]
	 */
	public function getMiddlewares(): array {
		return [];
	}
	
	private function getName(): string {
		preg_match('#(.*\\\\)?(.*)Controller$#', static::class, $matches);
		return $matches[2] ?? '';
	}

	/**
	 * @ignore
	 * @param string $property
	 * @return mixed
	 */
	public function __get(string $property) {
		$methods = [
			'baseUri' => 'getBaseUri',
			'app' => 'getApplication',
		];
		$methodName = $methods[$property] ?? '';
		if ($methodName && method_exists($this, $methodName)) {
			return $this->{$methodName}();
		}
		$className = static::class;
		Debug::logException(new \Exception("Undefined property '$property' for [$className]", E_NOTICE));
		return null;
	}

	/**
	 * @param Application $application
	 */
	public function setApplication(Application $application) {
		$this->app = $application;
	}
	
	/**
	 * @internal
	 * @return Application
	 */
	protected function getApplication(): Application {
		return $this->app;
	}

	/**
	 * @internal
	 * @return string
	 */
	protected function getBaseUri(): string {
		if (is_null($this->_baseUri)) {
			$this->_baseUri = '';
			if ($this->app instanceof Application) {
				$this->_baseUri = preg_replace('#/$#', '', $this->app->baseUri);
			}
		}
		return $this->_baseUri;
	}

	/**
	 * @param string $layout
	 */
	protected function setLayout(string $layout): void {
		$this->layout = 'Layout/'.$layout.'.twig';
	}

	/**
	 * {@inheritdoc}
	 */
	public function dispatch(string $path, ?array &$arguments = null): RequestHandlerInterface {
		$this->init();
		$dispatcher = $this->getDispatcher();
		return $dispatcher->dispatch($path, $arguments);
	}

	/**
	 * @param string $method
	 * @return RequestHandlerInterface
	 */
	protected function getActionHandler(string $method): RequestHandlerInterface {
		return new RequestHandler( function(RequestInterface $request, ...$arguments) use ($method) {
			if (!method_exists($this, $method)) {
				$className = static::class;
				throw new \RuntimeException("Undefined controller method '$method' for [$className]");
			}
			$this->request = $request;
			/* $this->container->setShared(RequestInterface::class, function() {
				return $this->request;
			}); */
			return $this->$method(...$arguments);
		});
	}
	
	// todo: move to somewhere
	/**
	 * @internal
	 * @return array
	 */
	protected function getLayoutParams(): array {
		$app = Application::getInstance();
		$params = [
			'layout' => $this->layout,
			'base' => $this->baseUri,
			'appMode' => $app->mode,
			'hasDebug' => $app->isMode(Application::MODE_DEV),
			'user' => $app->auth->getUser(),
		];
		if ($app->baseUri === '/dev') {
			return $params;
		}
		return array_replace($params, [
			'menu' => new Menu($this->request->uri),
		]);
	}

	/**
	 * @internal
	 */
	protected function getAssets(): string {
		$app = Application::getInstance();
		$filename = APP_PATH.'/../public/'.($app->isMode(Application::MODE_DEV) ? 'assets-dev.json' : 'assets.json');
		return $this->generateAssets($filename, '/assets/');
	}

	/**
	 * @internal
	 * @param string $filename
	 * @param string $path
	 * @return string
	 */
	protected function generateAssets(string $filename, string $path = '/'): string {
		/* if (!is_file($filename)) {
			throw new AssetsFileNotFoundException("Assets file not found '$filename'");
		} */
		$assets = json_decode(file_get_contents($filename))->main;
		if (!isset($assets->js)) {
			return '';
		}
		$scripts = !is_array($assets->js) ? [$assets->js] : $assets->js;
		$html = '';
		foreach ($scripts as $script) {
			$html .= '<script src="'.$path.$script.'"></script>';
		}
		return $html;
	}

	protected function getViewPath(): string {
		return $this->name;
	}

	/**
	 * @param string $view
	 * @param array $context
	 * @return ResponseInterface
	 * @throws ControllerRenderViewException
	 */
	protected function render(string $view, array $context = []): ResponseInterface {
		/** @var RendererInterface $renderer */
		$renderer = $this->container->get(RendererInterface::class);
		$viewPath = "{$this->getViewPath()}/{$view}";
		$context = array_replace($context, $this->getLayoutParams());
		try {
			return new Response( $renderer->render($viewPath, $context) );
		} catch (\RuntimeException $ex) {
			throw new ControllerRenderViewException($viewPath, static::class, $ex);
		}
	}
	
	/**
	 * @param string $uri
	 * @param array $queryParams
	 * @return ResponseInterface
	 */
	protected function redirectUri(string $uri, array $queryParams = []): ResponseInterface {
		if ($queryParams) {
			$uri .= '?'.http_build_query($queryParams);
		}
		return new Response('', 303, [
			'location' => $this->baseUri.$uri
		]);
	}

	/* *
	 * @param Throwable $exception
	 * @return ResponseInterface
	 */
	/* protected function errorPage(Throwable $exception): ResponseInterface {
		ob_start();
		Debug::dumpException($exception);
		return new Response(ob_get_clean());
	} */

}