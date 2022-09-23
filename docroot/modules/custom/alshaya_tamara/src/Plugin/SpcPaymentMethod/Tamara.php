<?php

namespace Drupal\alshaya_tamara\Plugin\SpcPaymentMethod;

use Drupal\alshaya_spc\AlshayaSpcPaymentMethodPluginBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Cache\CacheableMetadata;

/**
 * Tamara payment method for SPC.
 *
 * @AlshayaSpcPaymentMethod(
 *   id = "tamara",
 *   label = @Translation("Installments with Tamara"),
 * )
 */
class Tamara extends AlshayaSpcPaymentMethodPluginBase implements ContainerFactoryPluginInterface {

  use StringTranslationTrait;

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
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory'),
    );
  }

  /**
   * Tamara constructor.
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
  public function processBuild(array &$build) {
    // Get the installment count from the Alshaya Tamara module's config.
    $alshayaTamaraConfig = $this->configFactory->get('alshaya_tamara.settings');
    $build['#attached']['drupalSettings']['tamara']['installmentCount'] = $alshayaTamaraConfig->get('installmentCount');
    CacheableMetadata::createFromRenderArray($build)
      ->addCacheableDependency($alshayaTamaraConfig)
      ->applyTo($build);

    // Attach the libraries for tamara widgets.
    $build['#attached']['library'][] = 'alshaya_tamara/tamara_checkout';
    $build['#attached']['library'][] = 'alshaya_white_label/tamara';

    $build['#strings']['tamara_error'] = [
      'key' => 'tamara_error',
      'value' => $this->t('Your tamara order has been cancelled', [], ['context' => 'tamara']),
    ];
  }

}
