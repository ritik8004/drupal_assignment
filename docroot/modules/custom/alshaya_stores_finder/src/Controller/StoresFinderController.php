<?php

namespace Drupal\alshaya_stores_finder\Controller;

use Drupal\Core\Ajax\CssCommand;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Ajax\InvokeCommand;

/**
 * Class StoresFinderController.
 */
class StoresFinderController extends ControllerBase {

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
    $response->addCommand(new InvokeCommand('#row-' . $node->id(), 'trigger', ['click']));
    // Adding class for selection.
    $response->addCommand(new InvokeCommand('#row-' . $node->id(), 'addClass', ['selected']));
    // Hide the map view exposed filter.
    $response->addCommand(new CssCommand('.block-views-exposed-filter-blockstores-finder-page-3', ['display' => 'none']));
    // Show the list view exposed filter.
    $response->addCommand(new CssCommand('.block-views-exposed-filter-blockstores-finder-page-1', ['display' => 'block']));
    // Remove class.
    $response->addCommand(new InvokeCommand('.block-views-exposed-filter-blockstores-finder-page-3', 'removeClass', ['list-view-exposed']));
    // Add class.
    $response->addCommand(new InvokeCommand('.list-view-link', 'addClass', ['active']));

    // Add class.
    $response->addCommand(new InvokeCommand('.block-views-exposed-filter-blockstores-finder-page-1', 'addClass', ['current-view']));
    $response->addCommand(new InvokeCommand('.block-views-exposed-filter-blockstores-finder-page-3', 'removeClass', ['current-view']));

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
    $display = 'page_1';
    if ($view_type == 'map_view') {
      $display = 'page_3';
      $response->addCommand(new CssCommand('.block-views-exposed-filter-blockstores-finder-page-1', ['display' => 'none']));
      $response->addCommand(new CssCommand('.block-views-exposed-filter-blockstores-finder-page-3', ['display' => 'block']));
      $response->addCommand(new InvokeCommand('.map-view-link', 'addClass', ['active']));
      $response->addCommand(new InvokeCommand('.list-view-link', 'removeClass', ['active']));
      $response->addCommand(new InvokeCommand('.block-views-exposed-filter-blockstores-finder-page-1', 'removeClass', ['current-view']));
      $response->addCommand(new InvokeCommand('.block-views-exposed-filter-blockstores-finder-page-3', 'addClass', ['current-view']));
    }
    else {
      $response->addCommand(new CssCommand('.block-views-exposed-filter-blockstores-finder-page-3', ['display' => 'none']));
      $response->addCommand(new CssCommand('.block-views-exposed-filter-blockstores-finder-page-1', ['display' => 'block']));
      $response->addCommand(new InvokeCommand('.list-view-link', 'addClass', ['active']));
      $response->addCommand(new InvokeCommand('.map-view-link', 'removeClass', ['active']));
      $response->addCommand(new InvokeCommand('.block-views-exposed-filter-blockstores-finder-page-1', 'addClass', ['current-view']));
      $response->addCommand(new InvokeCommand('.block-views-exposed-filter-blockstores-finder-page-3', 'removeClass', ['current-view']));
    }
    $view = views_embed_view('stores_finder', $display);
    $response->addCommand(new ReplaceCommand('.view-stores-finder:first', $view));

    return $response;
  }

  /**
   * Get the store detail.
   *
   * @param \Drupal\Core\Entity\EntityInterface $node
   *   Node object.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   Ajax response.
   */
  public function storeDetail(EntityInterface $node) {
    $build = \Drupal::entityTypeManager()->getViewBuilder('node')->view($node);
    $response = new AjaxResponse();
    $response->addCommand(new ReplaceCommand('.view-stores-finder:first', $build));
    return $response;
  }

}
