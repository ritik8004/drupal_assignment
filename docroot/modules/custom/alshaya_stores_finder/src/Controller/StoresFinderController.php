<?php

namespace Drupal\alshaya_stores_finder\Controller;

use Drupal\Core\Ajax\AppendCommand;
use Drupal\Core\Ajax\CssCommand;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class Stores Finder Controller.
 */
class StoresFinderController extends ControllerBase {

  /**
   * Entity repository.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * StoresFinderController constructor.
   *
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   Entity repository.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   */
  public function __construct(EntityRepositoryInterface $entity_repository,
                              ConfigFactoryInterface $config_factory) {
    $this->entityRepository = $entity_repository;
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.repository'),
      $container->get('config.factory')
    );
  }

  /**
   * Ajax request on store finder map view.
   *
   * @param \Drupal\Core\Entity\EntityInterface $node
   *   Node object.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   Ajax response.
   */
  public function updateGlossaryView(EntityInterface $node) {
    $response = new AjaxResponse();
    $list_view = views_embed_view('stores_finder', 'page_1');
    $response->addCommand(new HtmlCommand('.view-display-id-page_2', $list_view));
    // Firing click event.
    $response->addCommand(new InvokeCommand('#row-' . $node->id() . ' .views-field-field-store-address', 'trigger', ['click']));
    // Adding class for selection.
    $response->addCommand(new InvokeCommand('.row-' . $node->id(), 'addClass', ['selected']));
    // Show the list view exposed filter.
    $response->addCommand(new CssCommand('[data-drupal-selector="views-exposed-form-stores-finder-page-1"]', ['display' => 'block']));
    // Add class.
    $response->addCommand(new InvokeCommand('.list-view-link', 'addClass', ['active']));

    $response->addCommand(new InvokeCommand('body', 'removeClass', ['store-finder-view']));

    return $response;

  }

  /**
   * Toggle the view type based on the display.
   *
   * @param string $view_type
   *   The of view.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   Ajax response.
   */
  public function toggleView($view_type = 'list_view') {
    $response = new AjaxResponse();
    // The store-finder glossary display.
    $display = 'page_2';
    if ($view_type == 'map_view') {
      $display = 'page_3';
      $response->addCommand(new InvokeCommand('.map-view-link', 'addClass', ['active']));
      $response->addCommand(new InvokeCommand('.list-view-link', 'removeClass', ['active']));
      // Remove store title from breadcrumb.
      $response->addCommand(new InvokeCommand(NULL, 'updateStoreFinderBreadcrumb'));
    }
    else {
      $response->addCommand(new InvokeCommand('.list-view-link', 'addClass', ['active']));
      $response->addCommand(new InvokeCommand('.map-view-link', 'removeClass', ['active']));
      // Remove store title from breadcrumb.
      $response->addCommand(new InvokeCommand(NULL, 'updateStoreFinderBreadcrumb'));
    }
    $view = views_embed_view('stores_finder', $display);
    $response->addCommand(new HtmlCommand('.view-stores-finder:first', $view));
    $response->addCommand(new InvokeCommand('body', 'removeClass', ['store-finder-view']));
    // Removing class for mobile from store list.
    $response->addCommand(new InvokeCommand('[data-drupal-selector="views-exposed-form-stores-finder-page-1"]', 'removeClass', ['mobile-store-detail']));

    // Clear value from search field.
    $response->addCommand(new InvokeCommand('[data-drupal-selector="views-exposed-form-stores-finder-page-1"] form #edit-geolocation-geocoder-google-places-api', 'val', ['']));

    // Hide the 'back to glossar' link and show list/map view link.
    $response->addCommand(new CssCommand('.list-view-link', ['display' => 'block']));
    $response->addCommand(new CssCommand('.map-view-link', ['display' => 'block']));
    $response->addCommand(new CssCommand('.back-to-glossary', ['display' => 'none']));

    return $response;
  }

  /**
   * Get the store detail.
   *
   * @param \Drupal\Core\Entity\EntityInterface $node
   *   Node object.
   * @param string $type
   *   Type from where user came.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   Ajax response.
   */
  public function storeDetail(EntityInterface $node, $type = 'glossary') {
    // Get the correct translated version of node.
    $node = $this->entityRepository->getTranslationFromContext($node);
    $build = $this->entityTypeManager()->getViewBuilder('node')->view($node);
    $response = new AjaxResponse();
    $response->addCommand(new HtmlCommand('.view-stores-finder:first', $build));
    $response->addCommand(new InvokeCommand('.body', 'removeClass', ['store-finder-view']));
    // Adding class for mobile for store detail.
    $response->addCommand(new InvokeCommand('[data-drupal-selector="views-exposed-form-stores-finder-page-1"]', 'addClass', ['mobile-store-detail']));

    // Add store finder title in breadcrumb.
    $url = Url::fromRoute('entity.node.canonical', ['node' => $node->id()])->toString();
    $store_finder_node_li = '<li><a href="' . $url . '">' . $node->getTitle() . '</a></li>';
    $response->addCommand(new AppendCommand('.block-system-breadcrumb-block ol', $store_finder_node_li));

    // Hide the list/map view link and show the glossary link.
    $response->addCommand(new CssCommand('.list-view-link', ['display' => 'none']));
    $response->addCommand(new CssCommand('.map-view-link', ['display' => 'none']));
    $response->addCommand(new CssCommand('[data-drupal-selector="views-exposed-form-stores-finder-page-1"] .back-to-glossary', ['display' => 'block']));

    $response->addCommand(new InvokeCommand(NULL, 'storeFinderDetailPageScrollTop'));

    return $response;
  }

  /**
   * Route to load the glossary view by ajax.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   Ajax response.
   */
  public function glossaryBack() {
    $response = new AjaxResponse();
    $glossary_view = views_embed_view('stores_finder', 'page_2');
    $response->addCommand(new HtmlCommand('.view-display-id-page_2', $glossary_view));

    return $response;
  }

}
