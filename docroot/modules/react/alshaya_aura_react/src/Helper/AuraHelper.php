<?php

namespace Drupal\alshaya_aura_react\Helper;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Class AuraHelper.
 *
 * @package Drupal\alshaya_aura_react\Helper
 */
class AuraHelper {
  /**
   * The current user making the request.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Entity Type Manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * AuraHelper constructor.
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   Current user object.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity Type Manager.
   */
  public function __construct(AccountProxyInterface $current_user,
                              EntityTypeManagerInterface $entity_type_manager) {
    $this->currentUser = $current_user;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Get user's AURA Status.
   *
   * @return string
   *   User's AURA Status.
   */
  public function getUserAuraStatus() {
    $uid = $this->currentUser->id();
    $user = $this->entityTypeManager->getStorage('user')->load($uid);
    $status = $user->get('field_aura_loyalty_status')->getString() ?? '';

    return $status;
  }

  /**
   * Get user's AURA Tier.
   *
   * @return string
   *   User's AURA Tier.
   */
  public function getUserAuraTier() {
    $uid = $this->currentUser->id();
    $user = $this->entityTypeManager->getStorage('user')->load($uid);
    $tier = $user->get('field_aura_tier')->getString() ?? '';

    return $tier;
  }

}
