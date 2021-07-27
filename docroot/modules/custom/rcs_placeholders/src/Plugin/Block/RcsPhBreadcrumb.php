<?php

namespace Drupal\rcs_placeholders\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a dynamic breadcrumb for commerce pages.
 *
 * @Block(
 *   id = "rcs_ph_breadcrumb",
 *   admin_label = @Translation("RCS Placeholders breadcrumb"),
 *   category = @Translation("RCS Placeholders"),
 * )
 */
class RcsPhBreadcrumb extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      '#markup' => '<div id="rcs-ph-breadcrumb" data-param-get-data="false"><span></span></div>',
    ];
  }

}
