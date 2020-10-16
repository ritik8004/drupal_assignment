<?php

namespace Drupal\alshaya_aura_react\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Cache\Cache;
use Drupal\mobile_number\MobileNumberUtilInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\alshaya_aura_react\Helper\AuraHelper;

/**
 * AlshayaLoyaltyController for loyalty club page.
 *
 * @package Drupal\alshaya_aura_react\Controller
 */
class AlshayaLoyaltyController extends ControllerBase {
  /**
   * Mobile utility.
   *
   * @var \Drupal\mobile_number\MobileNumberUtilInterface
   */
  protected $mobileUtil;

  /**
   * Aura Helper service object.
   *
   * @var Drupal\alshaya_aura_react\Helper\AuraHelper
   */
  protected $auraHelper;

  /**
   * AlshayaLoyaltyController constructor.
   *
   * @param \Drupal\mobile_number\MobileNumberUtilInterface $mobile_util
   *   Mobile utility.
   * @param Drupal\alshaya_aura_react\Helper\AuraHelper $aura_helper
   *   The aura helper service.
   */
  public function __construct(
    MobileNumberUtilInterface $mobile_util,
    AuraHelper $aura_helper
  ) {
    $this->mobileUtil = $mobile_util;
    $this->auraHelper = $aura_helper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('mobile_number.util'),
      $container->get('alshaya_aura_react.aura_helper')
    );
  }

  /**
   * View AURA Loyalty Club details.
   */
  public function loyaltyClub() {
    $cache_tags = [];
    $loyalty_benefits_config = $this->config('alshaya_aura_react.loyalty_benefits');
    $loyalty_benefits_content = $loyalty_benefits_config->get('loyalty_benefits_content');

    $settings = [
      'loyaltyBenefitsTitle' => [
        'title1' => $loyalty_benefits_config->get('loyalty_benefits_title1') ?? '',
        'title2' => $loyalty_benefits_config->get('loyalty_benefits_title2') ?? '',
      ],
      'loyaltyBenefitsContent' => $loyalty_benefits_content ? $loyalty_benefits_content['value'] : '',
      'config' => $this->auraHelper->getAuraConfig(),
    ];

    $cache_tags = Cache::mergeTags($cache_tags, $loyalty_benefits_config->getCacheTags());

    return [
      '#theme' => 'my_loyalty_club',
      '#strings' => $this->auraHelper->getStaticStrings(),
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
