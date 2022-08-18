<?php

namespace Drupal\alshaya_hello_member\Plugin\Block;

use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Provides My Accounts Hello Member block.
 *
 * @Block(
 *   id = "hello_member_benefits_block",
 *   admin_label = @Translation("Hello Member Benefits block")
 * )
 */
class HelloMemberBenefitsBlock extends MyAccountsHelloMemberBlock {

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    // Show block only on my accounts page if hello member is enabled.
    return AccessResult::allowedIf(
      $this->helloMemberHelper->isHelloMemberEnabled() && $account->isAuthenticated()
    );
  }

}
