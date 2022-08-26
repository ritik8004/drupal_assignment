<?php

namespace Drupal\rcs_placeholders\Service;

use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Service provides helper functions for the rcs path prefix.
 */
class RcsPhPathPrefixHelper {

  /**
   * Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs a new RcsPhPathPrefixHelper instance.
   *
   * @param \Drupal\Core\Entity\ConfigFactoryInterface $config_factory
   *   Config Factory service.
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->configFactory = $config_factory;
    $this->rcsConfig = $this->configFactory->get('rcs_placeholders.settings');
  }

  /**
   * Returns an array of reserved path prefixes.
   *
   * @return array
   *   Mapping of path prefixes with the bundle.
   */
  public function getRcsPathPrefixes() {
    $settings = $this->rcsConfig->getRawData();
    $prefixes = [];
    foreach ($settings as $key => $value) {
      if (!empty($value['path_prefix'])) {
        $prefixes[$key] = $value['path_prefix'];
      }
    }
    return $prefixes;
  }

}
