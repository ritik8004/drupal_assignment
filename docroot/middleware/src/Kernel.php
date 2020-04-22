<?php

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\RouteCollectionBuilder;

/**
 * Class Kernel.
 *
 * @package App
 */
class Kernel extends BaseKernel {

  use MicroKernelTrait;

  private const CONFIG_EXTS = '.{php,xml,yaml,yml}';

  /**
   * {@inheritdoc}
   */
  public function registerBundles(): iterable {
    $contents = require $this->getProjectDir() . '/config/bundles.php';
    foreach ($contents as $class => $envs) {
      if ($envs[$this->environment] ?? $envs['all'] ?? FALSE) {
        yield new $class();
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getProjectDir(): string {
    return \dirname(__DIR__);
  }

  /**
   * Configure container.
   */
  protected function configureContainer(ContainerBuilder $container,
                                        LoaderInterface $loader): void {
    $container->addResource(new FileResource($this->getProjectDir() . '/config/bundles.php'));
    $container->setParameter('container.dumper.inline_class_loader', TRUE);
    $confDir = $this->getProjectDir() . '/config';

    $loader->load($confDir . '/{packages}/*' . self::CONFIG_EXTS, 'glob');
    $loader->load($confDir . '/{packages}/' . $this->environment . '/**/*' . self::CONFIG_EXTS, 'glob');
    $loader->load($confDir . '/{services}' . self::CONFIG_EXTS, 'glob');
    $loader->load($confDir . '/{services}_' . $this->environment . self::CONFIG_EXTS, 'glob');
  }

  /**
   * Configure routes.
   */
  protected function configureRoutes(RouteCollectionBuilder $routes): void {
    $confDir = $this->getProjectDir() . '/config';

    $routes->import($confDir . '/{routes}/' . $this->environment . '/**/*' . self::CONFIG_EXTS, '/', 'glob');
    $routes->import($confDir . '/{routes}/*' . self::CONFIG_EXTS, '/', 'glob');
    $routes->import($confDir . '/{routes}' . self::CONFIG_EXTS, '/', 'glob');
  }

}
