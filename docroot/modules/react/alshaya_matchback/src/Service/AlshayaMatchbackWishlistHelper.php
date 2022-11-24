<?php

namespace Drupal\alshaya_matchback\Service;

use Drupal\alshaya_wishlist\Helper\WishListHelper;
use Drupal\node\NodeInterface;

/**
 * Overrides WishlistHelper service for matchback.
 */
class AlshayaMatchbackWishlistHelper extends WishListHelper {

  /**
   * {@inheritDoc}
   */
  public function showWishlistIconForProduct(NodeInterface $node, string $view_mode): bool {
    if ($view_mode === 'matchback') {
      return $this->configFactory->get('alshaya_matchback.settings')
        ->get('show_wishlist_icon_on_matchback');
    }

    return parent::showWishlistIconForProduct($node, $view_mode);
  }

}
