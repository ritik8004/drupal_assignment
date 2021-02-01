<?php

namespace Drupal\alshaya_olapic\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a 'Alshaya Olapic Home Widget' Block.
 *
 * @Block(
 *   id = "alshaya_olapic_home_widget",
 *   admin_label = @Translation("Alshaya Olapic Home Widget"),
 *   category = @Translation("Alshaya Olapic Home Widget"),
 * )
 */
class AlshayaOlapicHomeWidget extends BlockBase implements ContainerFactoryPluginInterface {
  const PAGE_TYPE = 'home';

  /**
   * The language manger service.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param string $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    LanguageManagerInterface $language_manager,
    ConfigFactoryInterface $config_factory
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->languageManager = $language_manager;
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition
  ) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('language_manager'),
      $container->get('config.factory'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $lang = $this->languageManager->getCurrentLanguage()->getId();
    $data_instance_field_name = 'olapic_' . self::PAGE_TYPE . '_' . $lang . '_data_instance';
    $form[$data_instance_field_name] = [
      '#type' => 'textfield',
      '#title' => $this->t('Olapic Home Data Instance'),
      '#description' => $this->t('Copy the data-instance value from the Olapic Portal'),
      '#default_value' => $this->configuration[$data_instance_field_name] ?? '',
      '#weight' => '1',
    ];
    return parent::buildConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $lang = $this->languageManager->getCurrentLanguage()->getId();
    $data_instance_field_name = 'olapic_' . self::PAGE_TYPE . '_' . $lang . '_data_instance';
    $this->configuration[$data_instance_field_name] = $form_state->getValue($data_instance_field_name);
    parent::submitConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $lang = $this->languageManager->getCurrentLanguage()->getId();
    $country_code = strtoupper(_alshaya_custom_get_site_level_country_code());
    $data_apikey_field_name = 'olapic_' . $lang . '_data_apikey';
    $data_instance_field_name = 'olapic_' . self::PAGE_TYPE . '_' . $lang . '_data_instance';
    $data_apikey = $this->configFactory->get('alshaya_olapic.settings')->get($data_apikey_field_name) ?? '';
    $data_instance = $this->configuration[$data_instance_field_name] ?? '';
    $data_lang = $lang . '_' . $country_code;
    $olapic_keys = [
      'data_apikey' => $data_apikey,
      'data_instance' => $data_instance,
      'data_lang' => $data_lang,
    ];
    return [
      '#type' => 'markup',
      '#markup' => '<div id="olapic_specific_widget"></div>',
      '#attached' => [
        'library' => 'alshaya_olapic/alshaya_olapic_widget',
        'drupalSettings' => [
          'olapic_keys' => $olapic_keys,
        ],
      ],
    ];
  }

}
