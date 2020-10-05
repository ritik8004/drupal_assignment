<?php

namespace Drupal\alshaya_aura_react\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Class AlshayaLoyaltyController.
 *
 * @package Drupal\alshaya_aura_react\Controller
 */
class AlshayaLoyaltyController extends ControllerBase {

  /**
   * View AURA Loyalty Club details.
   */
  public function loyaltyClub() {
    $cache_tags = [];

    return [
      '#theme' => 'my_loyalty_club',
      '#attached' => [
        'library' => [
          'alshaya_aura_react/alshaya_aura_loyalty_club',
          'alshaya_white_label/aura-loyalty-myaccount',
        ],
      ],
      '#cache' => [
        'tags' => $cache_tags,
      ],
    ];
  }

  /**
   * Returns page title.
   */
  public function getLoyaltyClubTitle() {
    return $this->t('My Aura');
  }

}
