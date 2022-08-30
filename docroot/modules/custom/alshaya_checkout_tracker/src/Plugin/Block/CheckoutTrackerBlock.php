<?php

namespace Drupal\alshaya_checkout_tracker\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Url;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'CheckoutTrackerBlock' block.
 *
 * @Block(
 *   id = "checkout_tracker_block",
 *   admin_label = @Translation("Checkout tracker block"),
 * )
 */
class CheckoutTrackerBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Stores the configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs a new CheckoutTrackerBlock plugin.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactoryInterface $config_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory')
    );
  }

  /**
   * Implements \Drupal\block\BlockBase::build().
   */
  public function build() {
    $bag = NULL;
    $checkout = NULL;
    $current_path = Url::fromRoute('<current>');
    if (str_contains($current_path, '/cart')) {
      $checkout = '';
      $bag = 'completed';
    }
    if (str_contains($current_path, '/checkout')) {
      $checkout = 'completed';
      $bag = 'completed';
    }
    return [
      '#theme' => 'checkout_tracker_block',
      '#steps' => [
        'bagclass' => $bag,
        'checkoutclass' => $checkout,
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return 0;
  }

}
