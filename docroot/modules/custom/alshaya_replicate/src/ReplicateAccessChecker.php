<?php

namespace Drupal\alshaya_replicate;

use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Entity\EntityAccessCheck;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\user\Access\PermissionAccessCheck;
use Symfony\Component\Routing\Route;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Access checker which checks entity create/view access.
 */
class ReplicateAccessChecker implements AccessInterface {

  /**
   * Permission Access Check.
   *
   * @var \Drupal\user\Access\PermissionAccessCheck
   */
  protected $permAccessChecker;

  /**
   * Entity Access Check.
   *
   * @var \Drupal\Core\Entity\EntityAccessCheck
   */
  protected $entityAccessChecker;

  /**
   * Config Factory Interface.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Creates a new ReplicateAccessChecker instance.
   *
   * @param \Drupal\user\Access\PermissionAccessCheck $permAccessChecker
   *   Permission Access Check.
   * @param \Drupal\Core\Entity\EntityAccessCheck $entityAccessChecker
   *   Entity Access Check.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory.
   */
  public function __construct(PermissionAccessCheck $permAccessChecker, EntityAccessCheck $entityAccessChecker, ConfigFactoryInterface $config_factory) {
    $this->permAccessChecker = $permAccessChecker;
    $this->entityAccessChecker = $entityAccessChecker;
    $this->configFactory = $config_factory;
  }

  /**
   * Checks user access of current route.
   */
  public function access(Route $route, RouteMatchInterface $route_match, AccountInterface $account) {
    $config_content_types = $this->configFactory->get('alshaya_replicate.settings')->get('content_types');
    $node_type = $route_match->getParameter('node')->bundle();
    // Return if node types does not matches with replicate config.
    if (!(in_array($node_type, $config_content_types))) {
      return AccessResult::neutral();
    }

    $create_fake_route = clone $route;
    $create_fake_route->setRequirement('_entity_access', $route->getDefault('entity_type_id') . '.create');
    $view_fake_route = clone $route;
    $view_fake_route->setRequirement('_entity_access', $route->getDefault('entity_type_id') . '.create');
    $permission_fake_route = clone $route;
    $permission_fake_route->setRequirements(['_permission' => 'replicate entities']);
    return $this->entityAccessChecker->access($view_fake_route, $route_match, $account)
      ->andIf($this->entityAccessChecker->access($create_fake_route, $route_match, $account))
      ->andIf($this->permAccessChecker->access($permission_fake_route, $account));
  }

  /**
   * {@inheritdoc}
   */
  public function applies(Route $route) {
  }

}
