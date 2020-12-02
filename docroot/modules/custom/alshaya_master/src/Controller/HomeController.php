<?php

namespace Drupal\alshaya_master\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Customer controller to add front page.
 */
class HomeController extends ControllerBase {

  /**
   * Returns the build for home page.
   *
   * @return array
   *   Build array.
   */
  public function home() {
    $build = [];

    // Get entity details to show from config.
    // @todo Create admin inteface to select the entity for home page.
    $entityDetails = $this->config('alshaya_master.home')->get('entity');

    if (!empty($entityDetails)) {
      $view_builder = $this->entityTypeManager()->getViewBuilder($entityDetails['entity_type']);
      $storage = $this->entityTypeManager()->getStorage($entityDetails['entity_type']);
      $entity = $storage->load($entityDetails['id']);
      $build = $view_builder->view($entity, $entityDetails['view_mode']);
    }

    return $build;
  }

}
