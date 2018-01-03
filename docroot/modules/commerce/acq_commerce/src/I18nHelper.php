<?php

namespace Drupal\acq_commerce;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Language\LanguageManagerInterface;

/**
 * Class I18nHelper.
 */
class I18nHelper {

  /**
   * Stores the alshaya_api settings config array.
   *
   * @var array
   */
  protected $config;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Constructs a new I18nUtility object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   */
  public function __construct(ConfigFactoryInterface $config_factory, LanguageManagerInterface $language_manager) {
    $this->configFactory = $config_factory;
    $this->languageManager = $language_manager;
  }

  /**
   * Helper method to get mapping of all store ids and language codes.
   *
   * @return array
   *   Mapping array.
   */
  public function getStoreLanguageMapping() {
    $mapping = [];

    $languages = $this->languageManager->getLanguages();

    // Prepare the alternate locale data.
    foreach ($languages as $lang => $language) {
      // For default language, we access the config directly.
      if ($lang == $this->languageManager->getDefaultLanguage()->getId()) {
        $config = $this->configFactory->get('acq_commerce.store');
      }
      // We get store id from translated config for other languages.
      else {
        $config = $this->languageManager->getLanguageConfigOverride($lang, 'acq_commerce.store');
      }

      $mapping[$lang] = $config->get('store_id');
    }

    return $mapping;
  }
}