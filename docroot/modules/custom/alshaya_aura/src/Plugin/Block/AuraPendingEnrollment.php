<?php

namespace Drupal\alshaya_aura\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides `AURA Pending Enrollment` block.
 *
 * @Block(
 *   id = "aura_pending_enrollment",
 *   admin_label = @Translation("AURA Pending Enrollment")
 * )
 */
class AuraPendingEnrollment extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container,
                                array $configuration,
                                $plugin_id,
                                $plugin_definition) {
    return new static($configuration,
      $plugin_id,
      $plugin_definition
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      '#theme' => 'aura_pending_enrollment',
    ];
  }

}
