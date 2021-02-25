<?php

namespace Drupal\alshaya_bazaar_voice\Service;

use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Manage Alshaya bazaar voice feature.
 */
class AlshayaBazaarVoice {

  /**
   * Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * BazaarVoiceApiWrapper constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory.
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->configFactory = $config_factory;
  }

  /**
   * Helper function to get the sorting options from configs.
   */
  public function getSortingOptions() {
    $available_options = [];

    $config = $this->configFactory->get('bazaar_voice_sort_review.settings');
    $sort_options = $config->get('sort_options');
    $sort_option_labels = $config->get('sort_options_labels');

    if (!empty($sort_option_labels)) {
      foreach ($sort_option_labels as $key => $value) {
        if ($key == 'none') {
          $available_options[$key] = $sort_option_labels[$key];
        }
        $val = explode(':', $value['value']);
        if (array_search($val[0], $sort_options, TRUE)) {
          $available_options[$key] = $sort_option_labels[$key];
        }
      }
    }

    return array_values($available_options);
  }

}
