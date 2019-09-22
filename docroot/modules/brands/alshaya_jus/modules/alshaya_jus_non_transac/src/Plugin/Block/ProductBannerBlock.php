<?php

namespace Drupal\alshaya_jus_non_transac\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\Renderer;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'ProductBannerBlock' block.
 *
 * @Block(
 *  id = "product_banner_block",
 *  admin_label = @Translation("Product banner Block"),
 * )
 */
class ProductBannerBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\Renderer
   */
  protected $renderer;

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * ProductBannerBlock constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param string $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Render\Renderer $renderer
   *   Ther Render.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The current route match.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              Renderer $renderer,
                              RouteMatchInterface $route_match) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->renderer = $renderer;
    $this->routeMatch = $route_match;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('renderer'),
      $container->get('current_route_match')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $parameters = $this->routeMatch->getParameters()->get('arg_0');
    $build = [];
    $view = views_embed_view('product_list', 'block_1', $parameters);
    $build['#markup'] = $this->renderer->render($view);

    return $build;
  }

}
