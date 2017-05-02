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
        'library' => 'acq_cart/acq-cart-custom-js',
      ],
    ];
    return $output;
  }

}
