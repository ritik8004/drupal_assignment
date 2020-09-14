<?php

namespace Drupal\alshaya_aura\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides `Pending Full Enrollment` block.
 *
 * @Block(
 *   id = "pending_full_enrollment",
 *   admin_label = @Translation("Pending Full Enrollment")
 * )
 */
class PendingFullEnrollment extends BlockBase implements ContainerFactoryPluginInterface {

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
      '#theme' => 'pending_full_enrollment',
    ];
  }

}
