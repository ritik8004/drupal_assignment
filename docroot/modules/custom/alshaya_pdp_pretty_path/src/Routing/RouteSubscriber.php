<?php

namespace Drupal\alshaya_pdp_pretty_path\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Alter pdp routes, adding a parameter.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * Entity Type Manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a RouteSubscriber object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity Type Manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function alterRoutes(RouteCollection $collection) {
    try {
      $routeName = 'entity.node.canonical';
      $sourceRoute = $collection->get($routeName);

      if ($sourceRoute) {
        if (strpos($sourceRoute->getPath(), '{color}') === FALSE) {
          $sourceRoute->setPath($sourceRoute->getPath() . '/{color}');
        }
        $sourceRoute->setDefault('color', '');
        $sourceRoute->setRequirement('color', '.*');
      }
    }
    catch (\Exception $e) {

    }
  }

}
