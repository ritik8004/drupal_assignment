<?php

namespace Drupal\alshaya_aura_react\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Cache\Cache;
use Drupal\mobile_number\MobileNumberUtilInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\alshaya_aura_react\Helper\AuraHelper;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Access\AccessResult;

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
   * Module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * AlshayaLoyaltyController constructor.
   *
   * @param \Drupal\mobile_number\MobileNumberUtilInterface $mobile_util
   *   Mobile utility.
   * @param Drupal\alshaya_aura_react\Helper\AuraHelper $aura_helper
   *   The aura helper service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Module handler.
   */
  public function __construct(
    MobileNumberUtilInterface $mobile_util,
    AuraHelper $aura_helper,
    ModuleHandlerInterface $module_handler
  ) {
    $this->mobileUtil = $mobile_util;
    $this->auraHelper = $aura_helper;
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('mobile_number.util'),
      $container->get('alshaya_aura_react.aura_helper'),
      $container->get('module_handler')
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
    $this->moduleHandler->loadInclude('alshaya_aura_react', 'inc', 'alshaya_aura_react.static_strings');

    return [
      '#theme' => 'my_loyalty_club',
      '#strings' => _alshaya_aura_static_strings(),
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
    return $this->t('My AURA');
  }

  /**
   * Helper method to check access.
   *
   * @return \Drupal\Core\Access\AccessResult
   *   Return access result object.
   */
  public function checkAccess() {
    return AccessResult::allowedIf($this->auraHelper->isAuraEnabled());
  }

}
