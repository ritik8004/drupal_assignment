<?php

namespace Drupal\rcs_placeholders\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a decoupled navigation menu block.
 *
 * @Block(
 *   id = "rcs_ph_navigation_menu",
 *   admin_label = @Translation("RCS Placeholders navigation menu block"),
 *   category = @Translation("RCS Placeholders"),
 * )
 */
class RcsPhNavigationMenu extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      '#markup' => '<div id="rcs-ph-navigation_menu"><span></span></div>',
    ];
  }

}
