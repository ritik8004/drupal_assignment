<?php

namespace Drupal\alshaya_aura\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Class AlshayaLoyaltyController.
 *
 * @package Drupal\alshaya_aura\Controller
 */
class AlshayaLoyaltyController extends ControllerBase {

  /**
   * View AURA Loyalty Club details.
   */
  public function loyaltyClub() {
    return [
      '#theme' => 'my_loyalty_club',
    ];
  }

  /**
   * Returns page title.
   */
  public function getLoyaltyClubTitle() {
    return $this->t('My Loyalty Club');
  }

}
