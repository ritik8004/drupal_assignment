<?php

namespace Drupal\alshaya_tamara\Plugin\SpcPaymentMethod;

use Drupal\alshaya_spc\AlshayaSpcPaymentMethodPluginBase;
use Drupal\alshaya_tamara\AlshayaTamaraWidgetHelper;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Tamara payment method for SPC.
 *
 * @AlshayaSpcPaymentMethod(
 *   id = "tamara",
 *   label = @Translation("Instalments with Tamara"),
 * )
 */
class Tamara extends AlshayaSpcPaymentMethodPluginBase implements ContainerFactoryPluginInterface {

  use StringTranslationTrait;

  /**
   * Tamara widget Helper.
   *
   * @var \Drupal\alshaya_tamara\AlshayaTamaraWidgetHelper
   */
  protected $tamaraWidgetHelper;

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
      $container->get('alshaya_tamara.widget_helper'),
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
   * @param \Drupal\alshaya_tamara\AlshayaTamaraWidgetHelper $tamara_widget_helper
   *   Tamara api Helper.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              AlshayaTamaraWidgetHelper $tamara_widget_helper) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->tamaraWidgetHelper = $tamara_widget_helper;
  }

  /**
   * {@inheritdoc}
   */
  public function isAvailable() {
    // @todo we need to connect with MDC to check if tamara is available.
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function processBuild(array &$build) {
    $this->tamaraWidgetHelper->getTamaraPaymentBuild($build, 'checkout');

    $build['#strings']['tamara_error'] = [
      'key' => 'tamara_error',
      'value' => $this->t('Your tamara order has been cancelled', [], ['context' => 'tamara']),
    ];
  }

}
