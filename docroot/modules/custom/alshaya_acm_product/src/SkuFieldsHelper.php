<?php

namespace Drupal\alshaya_acm_product;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Language\LanguageManagerInterface;

/**
 * Class Sku Fields Helper.
 *
 * @package Drupal\alshaya_acm_product
 */
class SkuFieldsHelper {

  /**
   * Config id.
   *
   * @see alshaya_acm_product.fields_labels_n_error.yml in alshaya_pb_transac.
   */
  public const CONFIG_FIELD_LABELS_N_ERROR = 'alshaya_acm_product.fields_labels_n_error';

  /**
   * Read-only Config data for alshaya_acm_product.fields_labels_n_error.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $configFieldLabelsError;

  /**
   * Current interface language code.
   *
   * @var string
   */
  protected $langcode;

  /**
   * SkuFieldsHelper constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory service object.
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   *   The language manager service.
   */
  public function __construct(ConfigFactoryInterface $config_factory,
                              LanguageManagerInterface $languageManager) {
    $this->langcode = $languageManager->getCurrentLanguage()->getId();
    $this->configFieldLabelsError = $config_factory->get(self::CONFIG_FIELD_LABELS_N_ERROR);
  }

  /**
   * Get overridden value to use for error message.
   *
   * @param string $attribute_code
   *   Attribute code to check for.
   *
   * @return string|null
   *   Overridden value if available.
   */
  public function getOverriddenAttributeError($attribute_code) {
    return $this->getOverriddenAttributeValue($attribute_code, 'error');
  }

  /**
   * Get overridden value to use as label.
   *
   * @param string $attribute_code
   *   Attribute code to check for.
   *
   * @return string|null
   *   Overridden value if available.
   */
  public function getOverriddenAttributeLabel($attribute_code) {
    return $this->getOverriddenAttributeValue($attribute_code, 'label');
  }

  /**
   * Get overridden value to use as label when there is value selected.
   *
   * @param string $attribute_code
   *   Attribute code to check for.
   *
   * @return string|null
   *   Overridden value if available.
   */
  public function getOverriddenAttributeSelectedLabel($attribute_code) {
    return $this->getOverriddenAttributeValue($attribute_code, 'selected_label');
  }

  /**
   * Get overridden value for specific key.
   *
   * @param string $attribute_code
   *   Attribute code to check for.
   * @param string $config_key
   *   Key to get value for from config.
   *
   * @return string|null
   *   Overridden value if available.
   */
  protected function getOverriddenAttributeValue($attribute_code, $config_key) {
    $config = $this->configFieldLabelsError->get($attribute_code);
    if (empty($config) || empty($config[$this->langcode][$config_key])) {
      return NULL;
    }

    return $config[$this->langcode][$config_key];
  }

}
