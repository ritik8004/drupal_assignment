<?php

namespace Drupal\acq_sku;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Access controller for the SKU entity.
 *
 * @see \Drupal\acq_sku\Entity\SKU.
 */
class SKUAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    return match ($operation) {
      'view' => AccessResult::allowedIfHasPermission($account, 'view sku entity'),
          'edit' => AccessResult::allowedIfHasPermission($account, 'edit sku entity'),
          'delete' => AccessResult::allowedIfHasPermission($account, 'delete sku entity'),
          default => AccessResult::allowed(),
    };
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add sku entity');
  }

}
