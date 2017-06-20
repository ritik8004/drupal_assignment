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
   */
  public function __construct(EntityManager $entityManager,
                              RendererInterface $renderer,
                              CurrentPathStack $currentPath,
                              RouterInterface $router,
                              PathProcessorManager $pathProcessor,
                              LoggerChannelFactoryInterface $logger,
                              BlockManager $blockManager,
                              CurrentRouteMatch $currentRouteMatch) {
    parent::__construct($entityManager, $renderer, $currentPath, $router, $pathProcessor, $logger);
    $this->blockManager = $blockManager;
    $this->currentRouteMatch = $currentRouteMatch;
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
      $container->get('current_route_match')
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
    $isPlpPage = FALSE;
    $isPromoPage = FALSE;
    $isSearchPage = FALSE;
    $facetFields['facets_container'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => 'facets-hidden-container',
      ],
    ];;

    $parameters = UrlHelper::filterQueryParameters(\Drupal::request()->query->all());
    if (!empty($parameters) && isset($parameters['f'])) {
      foreach ($parameters['f'] as $key => $value) {
        // Add hidden form field for facet parameter.
        $facetFields['facets_container']['f[' . $key . ']'] = [
          '#type' => 'hidden',
          '#value' => $value,
          '#weight' => -1,
          '#attributes' => [
            'name' => 'f[' . $key . ']',
          ],
        ];
      }
    }

    $this->setPageType($isPlpPage, $isPromoPage, $isSearchPage);

    // If page is PLP or Promo, inject hidden field into product_list exposed
    // form.
    if ($isSearchPage) {
      $response->addCommand(new InsertCommand('.block-views-exposed-filter-blocksearch-page:not(.block-keyword-search-block) form .facets-hidden-container', $facetFields));
    }

    // If page is search, inject hidden field into search page exposed form.
    if ($isPlpPage || $isPromoPage) {
      $response->addCommand(new InsertCommand('.block-views-exposed-filter-blockalshaya-product-list-block-1 form .facets-hidden-container', $facetFields));
    }

    return $response;
  }

  /**
   * Helper function to set page type variables.
   *
   * @param bool $isPlpPage
   *   TRUE if current page is PLP.
   * @param bool $isPromoPage
   *   TRUE if current page is promotion content-type.
   * @param bool $isSearchPage
   *   TRUE if current page is search page.
   */
  protected function setPageType(&$isPlpPage, &$isPromoPage, &$isSearchPage) {
    $currentRouteName = $this->currentRouteMatch->getRouteName();
    if ($currentRouteName === 'entity.taxonomy_term.canonical') {
      $term = $this->currentRouteMatch->getParameter('taxonomy_term');
      $vocabId = $term->getVocabularyId();
      if ($vocabId === 'acq_product_category') {
        $isPlpPage = TRUE;
        return;
      }
    }
    elseif ($currentRouteName === 'entity.node.canonical') {
      $node = $this->currentRouteMatch->getParameter('node');
      $bundle = $node->bundle();
      if ($bundle === 'promotion') {
        $isPromoPage = TRUE;
        return;
      }
    }
    elseif ($currentRouteName === 'view.search.page') {
      $isSearchPage = TRUE;
      return;
    }
  }

}
