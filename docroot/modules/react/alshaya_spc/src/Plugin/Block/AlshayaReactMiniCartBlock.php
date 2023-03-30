<?php

namespace Drupal\alshaya_spc\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'AlshayaReactMiniCartBlock' block.
 *
 * @Block(
 *   id = "alshaya_react_mini_cart",
 *   admin_label = @Translation("Alshaya React Cart Mini Cart Block"),
 * )
 */
class AlshayaReactMiniCartBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container,
                                array $configuration,
                                $plugin_id,
                                $plugin_definition) {
    return new static($configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory'),
    );
  }

  /**
   * AlshayaReactMiniCartBlock constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              ConfigFactoryInterface $config_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $cache_tags = [];

    $currency_config = $this->configFactory->get('acq_commerce.currency');
    $cache_tags = Cache::mergeTags($cache_tags, $currency_config->getCacheTags());

    $settings = [];
    $settings['alshaya_spc']['currency_config'] = [
      'currency_code' => $currency_config->get('currency_code'),
      'currency_code_position' => $currency_config->get('currency_code_position'),
      'decimal_points' => $currency_config->get('decimal_points'),
    ];

    $cart_config = $this->configFactory->get('alshaya_acm.cart_config');
    $cache_tags = Cache::mergeTags($cache_tags, $cart_config->getCacheTags());
    $settings['alshaya_spc']['cart_storage_expiration'] = $cart_config->get('cart_storage_expiration') ?? 15;

    $product_config = $this->configFactory->get('alshaya_acm_product.settings');
    $cache_tags = Cache::mergeTags($cache_tags, $product_config->getCacheTags());
    $settings['alshaya_spc']['productExpirationTime'] = $product_config->get('local_storage_cache_time') ?? 60;

    $build = [
      '#type' => 'markup',
      '#markup' => '<div id="mini-cart-wrapper"></div><div id="cart_notification"></div>',
      '#attached' => [
        'library' => [
          'alshaya_spc/mini_cart',
        ],
        'drupalSettings' => $settings,
      ],
      '#cache' => [
        'contexts' => [
          'languages:' . LanguageInterface::TYPE_INTERFACE,
          'user',
        ],
        'tags' => $cache_tags,
      ],
    ];

    return $build;
  }

}
