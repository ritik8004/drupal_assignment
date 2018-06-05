<?php

namespace Drupal\alshaya_api;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Language\LanguageManagerInterface;

/**
 * Class AlshayaI18nHelper.
 */
class AlshayaI18nHelper {

  /**
   * Config Factory service object.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Constructs a new AlshayaI18nHelper object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   */
  public function __construct(ConfigFactoryInterface $config_factory,
                              LanguageManagerInterface $language_manager) {
    $this->configFactory = $config_factory;
    $this->languageManager = $language_manager;
  }

  /**
   * Get Drupal Language Code from Magento Language Code.
   *
   * @param string $code
   *   Language code provided by Magento.
   *
   * @return string
   *   Drupal Language code.
   */
  public function getLangcodeFromMagentoLanguage($code) {
    $mapping = array_flip($this->getMapping());

    return isset($mapping[$code])
      ? $mapping[$code]
      : $this->languageManager->getDefaultLanguage()->getId();
  }

  /**
   * Get mapping between Drupal language code and Magento language code.
   *
   * @return array
   *   Mapping between Drupal language code and Magento language code
   */
  private function getMapping() {
    static $mapping = [];

    if (empty($mapping)) {
      foreach ($this->languageManager->getLanguages() as $language) {
        $config = $language->isDefault()
          ? $this->configFactory->get('alshaya_api.settings')
          : $this->languageManager->getLanguageConfigOverride($language->getId(), 'alshaya_api.settings');

        $mapping[$language->getId()] = $config->get('magento_lang_prefix');
      }
    }

    return $mapping;
  }

}
