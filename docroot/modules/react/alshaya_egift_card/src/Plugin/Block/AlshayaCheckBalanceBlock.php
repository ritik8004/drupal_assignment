<?php

namespace Drupal\alshaya_egift_card\Plugin\Block;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\Component\Serialization\Json;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\alshaya_egift_card\Helper\EgiftCardHelper;

/**
 * Provides Balance Check block.
 *
 * @Block(
 *   id = "alshaya_check_balance",
 *   admin_label = @Translation("Alshaya Balance Check Block")
 * )
 */
class AlshayaCheckBalanceBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * EgiftCardHelper.
   *
   * @var \Drupal\alshaya_egift_card\Helper\EgiftCardHelper
   */
  protected $egiftCardHelper;

  /**
   * Constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param string $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\alshaya_egift_card\Helper\EgiftCardHelper $egiftCardHelper
   *   EgiftCardHelper.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              ConfigFactoryInterface $config_factory,
                              EgiftCardHelper $egiftCardHelper) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $config_factory;
    $this->egiftCardHelper = $egiftCardHelper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory'),
      $container->get('alshaya_egift_card.egift_card_helper'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'block_title' => $this->t('Check Card Balance'),
      'block_description' => $this->t('Keep loading your eGift card balance to use it for your purchase'),
      'button_value' => $this->t('CHECK BALANCE'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form['block_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Block title'),
      '#description' => $this->t('Title to be displayed for this block.'),
      '#default_value' => $this->configuration['block_title'],
    ];
    $form['block_description'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Block description'),
      '#description' => $this->t('Description to be displayed for this block.'),
      '#default_value' => $this->configuration['block_description'] ?? '',
    ];
    $form['button_value'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Button Name'),
      '#description' => $this->t('Button name to be displayed on this block.'),
      '#default_value' => $this->configuration['button_value'] ?? '',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['block_title'] = $form_state->getValue('block_title');
    $this->configuration['block_description'] = $form_state->getValue('block_description');
    $this->configuration['button_value'] = $form_state->getValue('button_value');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      '#theme' => 'egift_balance_check_block',
      '#block_title' => $this->configuration['block_title'],
      '#block_description' => $this->configuration['block_description'],
      '#attached' => [
        'library' => [
          'alshaya_egift_card/alshaya_egift_check_balance',
          'alshaya_white_label/egift-landing-page',
        ],
      ],
    ];
  }

}
