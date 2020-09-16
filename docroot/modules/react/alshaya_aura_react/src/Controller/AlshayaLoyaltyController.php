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

    $settings['alshaya_aura'] = [
      'user_details' => $this->getUserDetails(),
    ];

    return [
      '#theme' => 'my_loyalty_club',
      '#attached' => [
        'library' => [
          'alshaya_aura_react/alshaya_aura_loyalty_club',
        ],
        'drupalSettings' => $settings,
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
    return $this->t('My Loyalty Club');
  }

  /**
   * Get user details.
   *
   * @return array
   *   Array of user details.
   */
  private function getUserDetails() {
    $userDetails = [];
    $uid = $this->currentUser()->id();

    if (!$this->currentUser()->isAuthenticated()) {
      $userDetails = ['id' => $uid];
      return $userDetails;
    }

    $user = $this->entityTypeManager()->getStorage('user')->load($uid);
    $userDetails = [
      'id' => $uid,
      'email' => $this->currentUser()->getEmail(),
      'loyaltyStatus' => $user->get('field_aura_loyalty_status')->getString(),
    ];

    return $userDetails;
  }

}
