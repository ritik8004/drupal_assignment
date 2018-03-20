<?php

namespace Drupal\alshaya_search\Controller;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Ajax\InsertCommand;
use Drupal\Core\Routing\CurrentRouteMatch;
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
use Symfony\Component\HttpFoundation\RequestStack;

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
   * The current route matcher service.
   *
   * @var \Drupal\Core\Routing\CurrentRouteMatch
   */
  protected $currentRouteMatch;

  /**
   * Request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Constructs a FacetBlockAjaxController object.
   *
   * @param \Drupal\Core\Entity\EntityManager $entityManager
   *   The Entity Manager Service.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   * @param \Drupal\Core\Path\CurrentPathStack $currentPath
   *   The current path stack service.
   * @param \Symfony\Component\Routing\RouterInterface $router
   *   The router service.
   * @param \Drupal\Core\PathProcessor\PathProcessorManager $pathProcessor
   *   The path processor service.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger
   *   The logger service.
   * @param \Drupal\Core\Block\BlockManager $blockManager
   *   The Block manager service.
   * @param \Drupal\Core\Routing\CurrentRouteMatch $currentRouteMatch
   *   The current route matcher service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   Request stack.
   */
  public function __construct(EntityManager $entityManager,
                              RendererInterface $renderer,
                              CurrentPathStack $currentPath,
                              RouterInterface $router,
                              PathProcessorManager $pathProcessor,
                              LoggerChannelFactoryInterface $logger,
                              BlockManager $blockManager,
                              CurrentRouteMatch $currentRouteMatch,
                              RequestStack $request_stack) {
    parent::__construct($entityManager, $renderer, $currentPath, $router, $pathProcessor, $logger);
    $this->blockManager = $blockManager;
    $this->currentRouteMatch = $currentRouteMatch;
    $this->requestStack = $request_stack;
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
      $container->get('plugin.manager.block'),
      $container->get('current_route_match'),
      $container->get('request_stack')
    );
  }

  /**
   * Override the default controller function.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   Ajax processing for exposed forms on search & PLP pages.
   */
  public function ajaxFacetBlockView(Request $request) {
    $response = parent::ajaxFacetBlockView($request);
    $is_plp_page = FALSE;
    $is_promo_page = FALSE;
    $is_search_page = FALSE;
    $facet_fields['facets_container'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => 'facets-hidden-container',
      ],
    ];

    $parameters = UrlHelper::filterQueryParameters(\Drupal::request()->query->all());
    if (!empty($parameters) && isset($parameters['f'])) {
      foreach ($parameters['f'] as $key => $value) {
        // Add hidden form field for facet parameter.
        $facet_fields['facets_container']['f[' . $key . ']'] = [
          '#type' => 'hidden',
          '#value' => $value,
          '#weight' => -1,
          '#attributes' => [
            'name' => 'f[' . $key . ']',
          ],
        ];
      }
    }

    $this->setPageType($is_plp_page, $is_promo_page, $is_search_page);

    // If page is search, inject hidden field into search page exposed form.
    if ($is_search_page) {
      $response->addCommand(new InsertCommand('.block-views-exposed-filter-blocksearch-page:not(.block-keyword-search-block) form .facets-hidden-container', $facet_fields));
    }

    // If page is PLP, inject hidden field into product_list exposed form.
    if ($is_plp_page) {
      $response->addCommand(new InsertCommand('.block-views-exposed-filter-blockalshaya-product-list-block-1 form .facets-hidden-container', $facet_fields));
    }

    // If page is PLP, inject hidden field into product_list exposed form.
    if ($is_promo_page) {
      $response->addCommand(new InsertCommand('.block-views-exposed-filter-blockalshaya-product-list-block-2 form .facets-hidden-container', $facet_fields));
    }

    return $response;
  }

  /**
   * Helper function to set page type variables.
   *
   * @param bool $is_plp_page
   *   TRUE if current page is PLP.
   * @param bool $is_promo_page
   *   TRUE if current page is promotion content-type.
   * @param bool $is_search_page
   *   TRUE if current page is search page.
   */
  protected function setPageType(&$is_plp_page, &$is_promo_page, &$is_search_page) {
    $current_route_name = $this->currentRouteMatch->getRouteName();

    // If facet ajax request.
    if ($current_route_name === 'facets.block.ajax') {
      // Get master/original request and route.
      $master_request = $this->requestStack->getMasterRequest();
      $master_route = $master_request->attributes->get('_route');
      // If mater request is term page.
      if ($master_route === 'entity.taxonomy_term.canonical') {
        $term = $master_request->attributes->get('taxonomy_term');
        $is_plp_page = $this->checkPageType($master_route, $term);
      }
      elseif ($master_route === 'entity.node.canonical') {
        $node = $master_request->attributes->get('node');
        $is_promo_page = $this->checkPageType($master_route, $node);
      }
      elseif ($master_route === 'view.search.page') {
        $is_search_page = TRUE;
      }

      return;
    }

    if ($current_route_name === 'entity.taxonomy_term.canonical') {
      $term = $this->currentRouteMatch->getParameter('taxonomy_term');
      $is_plp_page = $this->checkPageType($current_route_name, $term);
    }
    elseif ($current_route_name === 'entity.node.canonical') {
      $node = $this->currentRouteMatch->getParameter('node');
      $is_promo_page = $this->checkPageType($current_route_name, $node);
    }
    elseif ($current_route_name === 'view.search.page') {
      $is_search_page = TRUE;
    }
  }

  /**
   * Check if the given route and parameter matches.
   *
   * @param string $route_name
   *   Route name.
   * @param mixed $parameter
   *   Parameter for the route (Node/Term).
   *
   * @return bool
   *   TRUE/FALSE.
   */
  protected function checkPageType($route_name, $parameter) {
    if ($route_name === 'entity.taxonomy_term.canonical') {
      $vocabId = $parameter->getVocabularyId();
      if ($vocabId === 'acq_product_category') {
        return TRUE;
      }
    }
    elseif ($route_name === 'entity.node.canonical') {
      $bundle = $parameter->bundle();
      if ($bundle === 'acq_promotion') {
        return TRUE;
      }
    }
    elseif ($route_name === 'view.search.page') {
      return TRUE;
    }

    return FALSE;
  }

}
