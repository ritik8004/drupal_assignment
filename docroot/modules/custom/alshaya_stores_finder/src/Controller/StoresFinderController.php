<?php

namespace Drupal\alshaya_stores_finder\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
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
  public function updateGlossarView(EntityInterface $node) {
    $response = new AjaxResponse();
    $list_view = views_embed_view('stores_finder', 'page_3');
    $response->addCommand(new HtmlCommand('.view-display-id-page_2', $list_view));
    // Firing click event.
    $response->addCommand(new InvokeCommand('#row-' . $node->id(), 'trigger', ['click']));
    // Adding class for selection.
    $response->addCommand(new InvokeCommand('#row-' . $node->id(), 'addClass', ['selected']));

    return $response;
  }

}
