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
use Drupal\node\NodeInterface;
use Drupal\path_alias\AliasManagerInterface;
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
   * The path alias manager.
   *
   * @var \Drupal\path_alias\AliasManagerInterface
   */
  protected $aliasManager;

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
   * @param \Drupal\path_alias\AliasManagerInterface $alias_manager
   *   The path alias manager.
   */
  public function __construct(
    MobileNumberUtilInterface $mobile_util,
    AuraHelper $aura_helper,
    ModuleHandlerInterface $module_handler,
    AuraApiHelper $api_helper,
    LanguageManagerInterface $language_manager,
    TokenInterface $token,
    AliasManagerInterface $alias_manager
  ) {
    $this->mobileUtil = $mobile_util;
    $this->auraHelper = $aura_helper;
    $this->moduleHandler = $module_handler;
    $this->apiHelper = $api_helper;
    $this->languageManager = $language_manager;
    $this->token = $token;
    $this->aliasManager = $alias_manager;
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
      $container->get('path_alias.manager'),
    );
  }

  /**
   * View AURA Loyalty Club details.
   */
  public function loyaltyClub() {
    $cache_tags = [];
    $loyalty_benefits_config = $this->config('alshaya_aura_react.loyalty_benefits');

    $settings = [
      'loyaltyBenefitsTitle' => [
        'title1' => $loyalty_benefits_config->get('loyalty_benefits_title1') ?? '',
        'title2' => $loyalty_benefits_config->get('loyalty_benefits_title2') ?? '',
      ],
      'allBrands' => $this->apiHelper->getAuraApiConfig(
        [AuraDictionaryApiConstants::APC_BRANDS],
        $this->languageManager->getCurrentLanguage()->getId(),
      )[AuraDictionaryApiConstants::APC_BRANDS],
      'config' => $this->auraHelper->getAuraConfig(),
      // Set context for the Aura banner.
      'context' => 'my_aura',
    ];

    // Get the static page url for the loyalty page.
    $static_page_nid = $this->configFactory->getEditable('alshaya_aura_react.loyalty_benefits')->get('loyalty_static_content_node');
    if ($static_page_nid) {
      $node = $this->entityTypeManager()->getStorage('node')->load($static_page_nid);
      if ($node instanceof NodeInterface && $node->bundle() === 'static_html') {
        $loyalty_asset = [];
        // Get CSS from the node field.
        $css = $node->get('field_css')->getString();
        // Validate if CSS data is available.
        if (!empty($css)) {
          $loyalty_asset[] = [[
            '#tag' => 'style',
            '#value' => $css,
          ],
            'cpath',
          ];
        }
        // Get JS from the node field.
        $js = $node->get('field_javascript')->getString();
        // Validate if JS data is available.
        if (!empty($js)) {
          $loyalty_asset[] = [[
            '#tag' => 'script',
            '#value' => $js,
          ],
            'spath',
          ];
        }
        $path = $this->aliasManager->getAliasByPath('/node/' . $node->id());
        // If path is valid then update the settings.
        if ($path) {
          $settings['loyaltyStaticPageUrl'] = trim($path, '/');
          // Adding cache tag of aura landing page.
          $cache_tags = Cache::mergeTags($cache_tags, $node->getCacheTags());
        }
      }
    }

    $cache_tags = Cache::mergeTags($cache_tags, $loyalty_benefits_config->getCacheTags());
    $this->moduleHandler->loadInclude('alshaya_aura_react', 'inc', 'alshaya_aura_react.static_strings');

    // Add the description meta data and title tag for the Aura landing page.
    $html_head = [
      [
        [
          '#tag' => 'meta',
          '#attributes' => [
            'name' => 'description',
            'content' => $this->token->replace($this->t('Download AURA now to get personalized offers and rewards for each purchase you make from [alshaya_seo:brand_name] online or instore in [alshaya_seo:cities] and all of [alshaya_seo:country]', [], [
              'context' => 'aura',
            ])),
          ],
        ],
        'description',
      ],
      [
        [
          '#tag' => 'title',
          '#value' => $this->token->replace($this->t('Buy and get rewards and exclusive offers with AURA | [alshaya_seo:brand_name]', [], [
            'context' => 'aura',
          ])),
        ],
        'title',
      ],
    ];

    // Merge loyalty_asset array into If assets are available in Node.
    if (!empty($loyalty_asset)) {
      $html_head = array_merge($html_head, $loyalty_asset);
    }

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
        'html_head' => $html_head,
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
