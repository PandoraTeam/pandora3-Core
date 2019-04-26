<?php
namespace Pandora3\Core\Controller;

// temporary
use App\Widgets\Menu\Menu;

use Throwable;
use Closure;
use Pandora3\Core\Application\Application;
use Pandora3\Core\Controller\Exception\ControllerRenderViewException;
use Pandora3\Core\Interfaces\RequestDispatcherInterface;
use Pandora3\Core\Interfaces\RequestHandlerInterface;
use Pandora3\Core\Container\Container;
use Pandora3\Core\Container\Exception\ContainerException;
use Pandora3\Core\Debug\Debug;
use Pandora3\Core\Interfaces\ControllerInterface;
use Pandora3\Core\Interfaces\RequestInterface;
use Pandora3\Core\Interfaces\ResponseInterface;
use Pandora3\Core\Interfaces\RouterInterface;
use Pandora3\Core\Router\RequestHandler;
use Pandora3\Core\Router\Router;
use Pandora3\Core\Http\Response;
use Pandora3\Plugins\Twig\TwigRenderer;

/**
 * Class Controller
 * @package Pandora3\Core\Controller
 *
 * @property-read string $baseUri
 */
abstract class Controller implements ControllerInterface, RequestDispatcherInterface {

	/** @var Container $container */
	protected $container;

	/**
	 * @internal
	 * @var string $_baseUri
	 */
	protected $_baseUri;

	/** @var string $name */
	protected $name;

	/** @var string $layout */
	protected $layout = 'Layout/Main.twig';

	/** @var RequestInterface $request */
	protected $request;
	
	protected function init(): void {
		$this->container = new Container();
		$this->name = $this->getName();
		$this->dependencies();
	}

	protected function dependencies(): void {
		$this->container->set(RouterInterface::class, Router::class);
	}

	/**
	 * @return array
	 */
	public function getRoutes(): array {
		return [];
	}
	
	private function getName(): string {
		preg_match('#(.*\\\\)?(.*)Controller$#', get_class($this), $matches);
		return $matches[2] ?? '';
	}

	/**
	 * @internal
	 * @param string $property
	 * @return mixed
	 */
	public function __get(string $property) {
		$methods = [
			'baseUri' => 'getBaseUri',
		];
		$methodName = $methods[$property] ?? '';
		if ($methodName && method_exists($this, $methodName)) {
			return $this->{$methodName}();
		} else {
			return null;
			// throw new \Exception('Method or property does not exist'); todo:
		}
	}

	/**
	 * @internal
	 * @return string
	 */
	protected function getBaseUri(): string {
		if ($this->_baseUri === null) {
			$app = Application::getInstance();
			$this->_baseUri = preg_replace('#/$#', '', $app->baseUri);
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
	 * @param string $path
	 * @param array|null $arguments
	 * @throws ContainerException
	 * @return RequestHandlerInterface
	 */
	public function dispatch(string $path, &$arguments = null): RequestHandlerInterface {
		$this->init();
		$router = $this->container->get(RouterInterface::class);
		/** @var RouterInterface $router */
		foreach($this->getRoutes() as $routePath => $method) {
			$router->add($routePath, $this->getActionHandler($method));
		}
		return $router->dispatch($path, $arguments);
	}

	/**
	 * @param string $method
	 * @return RequestHandlerInterface
	 */
	protected function getActionHandler(string $method): RequestHandlerInterface {
		if (!method_exists($this, $method)) {
			$className = static::class;
			throw new \RuntimeException("Controller [$className] action method '$method' not exist");
		}

		return new RequestHandler( function(RequestInterface $request, ...$arguments) use ($method) {
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
			'menu' => new Menu(),
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

	// todo: use Renderer
	/**
	 * @param string $view
	 * @param array $context
	 * @return ResponseInterface
	 */
	protected function render(string $view, array $context = []): ResponseInterface {
		$renderer = new TwigRenderer(APP_PATH.'/Views');
		$renderer->addFunctions([
			'dump' => 'dump',
			'debugOutput' => function() {
				$output = \Dump::getOutput();
				return $output ? '<div class="debug-output">'.$output.'</div>' : '';
			},
			'assets' => Closure::fromCallable([$this, 'getAssets']),
		]);

		$viewPath = "{$this->getViewPath()}/{$view}.twig";
		$context = array_replace($context, $this->getLayoutParams());
		try {
			return new Response( $renderer->render($viewPath, $context) );
		} catch (\Throwable $ex) {
			$className = get_class($this);
			return $this->errorPage(new ControllerRenderViewException("Rendering view '$viewPath' failed for [$className]", E_WARNING, $ex));
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
		return new Response('', [
			'location' => $this->baseUri.$uri
		]);
	}

	/**
	 * @param Throwable $exception
	 * @return ResponseInterface
	 */
	protected function errorPage(Throwable $exception): ResponseInterface {
		ob_start();
		Debug::dumpException($exception);
		return new Response(ob_get_clean());
	}

}