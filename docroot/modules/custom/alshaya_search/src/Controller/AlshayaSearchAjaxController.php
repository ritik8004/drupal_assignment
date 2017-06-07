<?php

namespace Drupal\alshaya_search\Controller;

use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\facets\Controller\FacetBlockAjaxController;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Block\BlockManager;
use Drupal\Core\Entity\EntityManager;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Path\CurrentPathStack;
use Drupal\Core\PathProcessor\PathProcessorManager;
use Drupal\Core\Render\RendererInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Override facets AJAX controller to add selected filters as hidden fields.
 */
class AlshayaSearchAjaxController extends FacetBlockAjaxController {

  /**
   * The Block Manager service.
   *
   * @var \Drupal\Core\Block\BlockManager
   */
  protected $blockManager;

  /**
   * Constructs a FacetBlockAjaxController object.
   *
   * @param \Drupal\Core\Entity\EntityManager $entityManager
   *   The Entity Manager service.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The Renderer service.
   * @param \Drupal\Core\Path\CurrentPathStack $currentPath
   *   The current path service.
   * @param \Symfony\Component\Routing\RouterInterface $router
   *   The router service.
   * @param \Drupal\Core\PathProcessor\PathProcessorManager $pathProcessor
   *   The path processor service.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger
   *   The logger service.
   * @param \Drupal\Core\Block\BlockManager $blockManager
   *   The Block manager service.
   */
  public function __construct(EntityManager $entityManager,
                              RendererInterface $renderer,
                              CurrentPathStack $currentPath,
                              RouterInterface $router,
                              PathProcessorManager $pathProcessor,
                              LoggerChannelFactoryInterface $logger,
                              BlockManager $blockManager) {
    parent::__construct($entityManager, $renderer, $currentPath, $router, $pathProcessor, $logger);
    $this->blockManager = $blockManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.manager'),
      $container->get('renderer'),
      $container->get('path.current'),
      $container->get('router'),
      $container->get('path_processor_manager'),
      $container->get('logger.factory'),
      $container->get('plugin.manager.block')
    );
  }

  /**
   * Override the default controller function.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   Ajax response to re-render the exposed form.
   */
  public function ajaxFacetBlockView(Request $request) {
    $response = parent::ajaxFacetBlockView($request);

    $block_id = 'views_exposed_filter_block:search-page';
    $block = $this->blockManager->createInstance($block_id);
    $block_view = $block->build();
    $response->addCommand(new ReplaceCommand('.views-exposed-form-search-page', $block_view));

    return $response;
  }

}
