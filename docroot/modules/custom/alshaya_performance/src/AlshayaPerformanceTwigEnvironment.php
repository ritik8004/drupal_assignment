<?php

namespace Drupal\alshaya_performance;

use Drupal\Core\State\StateInterface;
use Drupal\Core\Template\TwigEnvironment;

/**
 * A class that defines a Twig environment for Drupal.
 *
 * Instances of this class are used to store the configuration
 * and extensions for twig templates.
 * In this class we are saving new values
 * (twig_extension_hash, twig_cache_prefix) in state
 * for key twig_extension_hash_prefix to avoid it save
 * during any web request.
 * it will be executed during drush crf --twig.
 *
 * @see core\vendor\twig\twig\lib\Twig\Environment.php
 */
class AlshayaPerformanceTwigEnvironment {

  /**
   * The state service.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * The template cache filename prefix.
   *
   * @var string
   */
  protected $twigExtensionHash = '';

  /**
   * AlshayaPerformanceTwigEnvironment constructor.
   *
   * @param string $twig_extension_hash
   *   The Twig extension hash.
   * @param \Drupal\Core\State\StateInterface $state
   *   The state service.
   */
  public function __construct($twig_extension_hash, StateInterface $state) {
    $this->twigExtensionHash = $twig_extension_hash;
    $this->state = $state;
  }

  /**
   * Generate twig extension hash and store in state.
   */
  public function generateTwigExtensionHash() {
    $current = [
      'twig_extension_hash' => $this->twigExtensionHash,
      // Generate a new prefix which invalidates any existing cached files.
      'twig_cache_prefix' => uniqid(),
    ];
    $this->state->set(TwigEnvironment::CACHE_PREFIX_METADATA_KEY, $current);
  }

}
