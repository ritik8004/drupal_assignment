<?php

namespace Drupal\alshaya_rcs_listing\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a dynamic term description for commerce pages.
 *
 * @Block(
 *   id = "rcs_term_description",
 *   admin_label = @Translation("Alshaya RCS Term Description"),
 *   category = @Translation("RCS Placeholders"),
 * )
 */
class AlshayaRcsTermDescription extends BlockBase implements ContainerFactoryPluginInterface {

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
      '#markup' => '<div class="c-page-title__description"><span>#rcs.category.description#</span></div>',
    ];
  }

}
