<?php

namespace Drupal\rcs_placeholders\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;

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
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $currentRequest;

  /**
   * Constructs a new RcsPhPathPrefixHelper instance.
   *
   * @param \Drupal\Core\Entity\ConfigFactoryInterface $config_factory
   *   Config Factory service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   Request stock service object.
   */
  public function __construct(ConfigFactoryInterface $config_factory,
                              RequestStack $request_stack) {
    $this->configFactory = $config_factory;
    $this->currentRequest = $request_stack->getCurrentRequest();
  }

  /**
   * Returns an array of reserved path prefixes.
   *
   * @return array
   *   Mapping of path prefixes with the bundle.
   */
  public function getRcsPathPrefixes() {
    $rcs_config = $this->configFactory->get('rcs_placeholders.settings');
    $settings = $rcs_config->getRawData();
    $prefixes = [];
    foreach ($settings as $key => $value) {
      if (!empty($value['path_prefix'])) {
        $prefixes[$key] = $value['path_prefix'];
      }
    }
    return $prefixes;
  }

}
