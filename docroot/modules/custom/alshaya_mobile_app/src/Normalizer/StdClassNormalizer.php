<?php

namespace Drupal\alshaya_mobile_app\Normalizer;

use Drupal\serialization\Normalizer\NormalizerBase;

/**
 * StdClass normalizer.
 */
class StdClassNormalizer extends NormalizerBase {

  /**
   * Constructs a StdClassNormalizer object.
   *
   * @param string|array $supported_interface_of_class
   *   The supported interface(s) or class(es).
   */
  public function __construct($supported_interface_of_class) {
    $this->supportedInterfaceOrClass = $supported_interface_of_class;
  }

  /**
   * {@inheritdoc}
   */
  public function normalize($object, $format = NULL, array $context = []) {
    return new \stdClass();
  }

}
