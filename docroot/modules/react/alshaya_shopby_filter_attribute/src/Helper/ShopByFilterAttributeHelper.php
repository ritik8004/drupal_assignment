<?php

namespace Drupal\alshaya_shopby_filter_attribute\Helper;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\alshaya_search_algolia\Helper\AlshayaAlgoliaSortHelper;
use Drupal\alshaya_algolia_react\Plugin\Block\AlshayaAlgoliaReactPLP;

/**
 * Helper class for Alshaya shop by filter/attribute navigation.
 *
 * @package Drupal\alshaya_shopby_filter_attribute\Helper
 */
class ShopByFilterAttributeHelper {

  /**
   * Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The language manger service.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * ShopByFilterAttributeHelper constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory service object.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager service.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    LanguageManagerInterface $language_manager
  ) {
    $this->configFactory = $config_factory;
    $this->languageManager = $language_manager;
  }

  /**
   * Get main_menu_attribute_navigation config.
   *
   * @return array
   *   Main menu attribute navigation config.
   */
  public function getShopByFilterAttributeConfigs(): array {
    // Get the current language.
    $lang = $this->languageManager->getCurrentLanguage()->getId();

    // Get the configs from config factory.
    $alshaya_shopby_filter_attribute_config = $this->configFactory->get('alshaya_shopby_filter_attribute.settings');

    return [
      'enabled' => $alshaya_shopby_filter_attribute_config->get('enabled'),
      'menuFilterAttributes' => $alshaya_shopby_filter_attribute_config->get('attributes'),
      'indexName' => AlshayaAlgoliaSortHelper::getAlgoliaIndexName($lang, AlshayaAlgoliaReactPLP::PAGE_TYPE),
    ];
  }

  /**
   * Helper to check if feature is enabled.
   *
   * @return bool
   *   TRUE/FALSE
   */
  public function isShopByFilterAttributeEnabled(): bool {
    return $this->configFactory->get('alshaya_shopby_filter_attribute.settings')->get('enabled');
  }

}
