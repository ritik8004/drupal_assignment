<?php

namespace Drupal\alshaya_aura_react\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Cache\Cache;
use Drupal\mobile_number\MobileNumberUtilInterface;
use Drupal\token\TokenInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\alshaya_aura_react\Helper\AuraHelper;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\alshaya_aura_react\Helper\AuraApiHelper;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\alshaya_aura_react\Constants\AuraDictionaryApiConstants;

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
   * The api helper object.
   *
   * @var Drupal\alshaya_aura_react\Helper\AuraApiHelper
   */
  protected $apiHelper;

  /**
   * Language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Token Interface.
   *
   * @var \Drupal\token\TokenInterface
   */
  protected $token;

  /**
   * AlshayaLoyaltyController constructor.
   *
   * @param \Drupal\mobile_number\MobileNumberUtilInterface $mobile_util
   *   Mobile utility.
   * @param Drupal\alshaya_aura_react\Helper\AuraHelper $aura_helper
   *   The aura helper service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Module handler.
   * @param Drupal\alshaya_aura_react\Helper\AuraApiHelper $api_helper
   *   Api helper object.
   * @param Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The form builder.
   * @param \Drupal\token\TokenInterface $token
   *   Token interface.
   */
  public function __construct(
    MobileNumberUtilInterface $mobile_util,
    AuraHelper $aura_helper,
    ModuleHandlerInterface $module_handler,
    AuraApiHelper $api_helper,
    LanguageManagerInterface $language_manager,
    TokenInterface $token
  ) {
    $this->mobileUtil = $mobile_util;
    $this->auraHelper = $aura_helper;
    $this->moduleHandler = $module_handler;
    $this->apiHelper = $api_helper;
    $this->languageManager = $language_manager;
    $this->token = $token;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('mobile_number.util'),
      $container->get('alshaya_aura_react.aura_helper'),
      $container->get('module_handler'),
      $container->get('alshaya_aura_react.aura_api_helper'),
      $container->get('language_manager'),
      $container->get('token'),
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
      'allBrands' => $this->apiHelper->getAuraApiConfig(
        [AuraDictionaryApiConstants::APC_BRANDS],
        $this->languageManager->getCurrentLanguage()->getId(),
      )[AuraDictionaryApiConstants::APC_BRANDS],
      'loyaltyBenefitsContent' => $loyalty_benefits_content ? $this->token->replace($loyalty_benefits_content['value']) : '',
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
   * Aura loyalty club guest page.
   */
  public function loyaltyClubGuest() {
    // Redirect to loyalty club user page for authenticated user.
    if ($this->currentUser()->isAuthenticated()) {
      return $this->redirect('alshaya_aura_react.my_loyalty_club');
    }

    return $this->loyaltyClub();
  }

  /**
   * Returns page title.
   */
  public function getLoyaltyClubTitle() {
    if ($this->currentUser()->isAuthenticated()) {
      return $this->t('My Aura');
    }

    return $this->t('About Aura');
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
