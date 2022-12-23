<?php

namespace Drupal\alshaya_xb\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides 'ErrorContainerBlock' block.
 *
 * @Block(
 *   id = "error_container_block",
 *   admin_label = @Translation("Error Container Block")
 * )
 */
class ErrorContainerBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Build for error container block.
   *
   * @inheritDoc
   */
  public function build() {
    $build = [
      '#type' => 'markup',
      '#markup' => '<div class="errors-container js-form-wrapper form-wrapper" data-drupal-selector="edit-errors-container" id="edit-errors-container"></div>',
      '#attached' => [
        'library' => [
          'alshaya_xb/alshaya_xb_js',
        ],
      ],
    ];

    return $build;
  }

  /**
   * Creates new instance for ErrorContainerBlock.
   *
   * @inheritDoc
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
    );
  }

}
