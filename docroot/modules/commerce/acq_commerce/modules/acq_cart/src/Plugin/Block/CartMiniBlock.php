<?php

namespace Drupal\acq_cart\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'CartMiniBlock' block.
 *
 * @Block(
 *   id = "cart_mini_block",
 *   admin_label = @Translation("Cart Mini Block"),
 * )
 */
class CartMiniBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param string $plugin_definition
   *   The plugin implementation definition.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

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
    // Something to show till we get the AJAX response back.
    $loading_message = $this->t('Loading ...');
    $output = [
      '#markup' => '<div id="mini-cart-wrapper">' . $loading_message . '</div>',
      '#attached' => [
        'library' => 'acq_cart/acq-cart-custom-js'
        ]
    ];
    return $output;
  }
}
