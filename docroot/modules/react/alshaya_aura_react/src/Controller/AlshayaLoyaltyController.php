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
    $loyalty_benefits_config = $this->config('alshaya_aura_react.loyalty_benefits_form');
    $loyalty_benefits_title = $loyalty_benefits_config->get('loyalty_benefits_title');
    $loyalty_benefits_content = $loyalty_benefits_config->get('loyalty_benefits_content');
    $settings = [
      'loyaltyBenefitsTitle' => $loyalty_benefits_title ? $loyalty_benefits_title['value'] : '',
      'loyaltyBenefitsContent' => $loyalty_benefits_content ? $loyalty_benefits_content['value'] : '',
    ];

    return [
      '#theme' => 'my_loyalty_club',
      '#attached' => [
        'library' => [
          'alshaya_aura_react/alshaya_aura_loyalty_club',
          'alshaya_white_label/aura-loyalty-myaccount',
        ],
        'drupalSettings' => [
          'aura' => $settings,
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
