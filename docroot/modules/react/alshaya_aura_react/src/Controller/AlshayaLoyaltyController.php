<?php

namespace Drupal\alshaya_aura_react\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Cache\Cache;
use Drupal\mobile_number\MobileNumberUtilInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class AlshayaLoyaltyController.
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
   * AlshayaLoyaltyController constructor.
   *
   * @param \Drupal\mobile_number\MobileNumberUtilInterface $mobile_util
   *   Mobile utility.
   */
  public function __construct(MobileNumberUtilInterface $mobile_util) {
    $this->mobileUtil = $mobile_util;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('mobile_number.util')
    );
  }

  /**
   * View AURA Loyalty Club details.
   */
  public function loyaltyClub() {
    $cache_tags = [];
    // Get country code.
    $country_code = _alshaya_custom_get_site_level_country_code();
    $loyalty_benefits_config = $this->config('alshaya_aura_react.loyalty_benefits');
    $loyalty_benefits_content = $loyalty_benefits_config->get('loyalty_benefits_content');
    $alshaya_master_config = $this->config('alshaya_master.mobile_number_settings');
    $alshaya_aura_config = $this->config('alshaya_aura_react.settings');

    $settings = [
      'loyaltyBenefitsTitle' => [
        'title1' => $loyalty_benefits_config->get('loyalty_benefits_title1') ?? '',
        'title2' => $loyalty_benefits_config->get('loyalty_benefits_title2') ?? '',
      ],
      'loyaltyBenefitsContent' => $loyalty_benefits_content ? $loyalty_benefits_content['value'] : '',
      'config' => [
        'appStoreLink' => $alshaya_aura_config->get('aura_app_store_link'),
        'googlePlayLink' => $alshaya_aura_config->get('aura_google_play_link'),
        'country_mobile_code' => $this->mobileUtil->getCountryCode($country_code),
        'mobile_maxlength' => $alshaya_master_config->get('maxlength'),
      ],
    ];

    $cache_tags = Cache::mergeTags($cache_tags, array_merge(
      $loyalty_benefits_config->getCacheTags(),
      $alshaya_master_config->getCacheTags(),
      $alshaya_aura_config->getCacheTags()
    ));

    return [
      '#markup' => '<div id="my-loyalty-club"></div>',
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
