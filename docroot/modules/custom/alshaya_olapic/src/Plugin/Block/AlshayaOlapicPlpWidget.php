<?php

namespace Drupal\alshaya_olapic\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Path\PathValidatorInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a 'Plp' Block.
 *
 * @Block(
 *   id = "alshaya_olapic_plp_widget",
 *   admin_label = @Translation("Alshaya Olapic Plp Widget"),
 *   category = @Translation("Alshaya Olapic Plp Widget"),
 * )
 */
class AlshayaOlapicPlpWidget extends BlockBase implements ContainerFactoryPluginInterface {
  const PAGE_TYPE = 'plp';
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
   * The Path Validator service.
   *
   * @var \Drupal\Core\Path\PathValidatorInterface
   */
  protected $pathValidator;

  /**
   * The request stack service.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

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
   * @param \Drupal\Core\Path\PathValidatorInterface $pathValidator
   *   The path validator service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request stack service.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    LanguageManagerInterface $language_manager,
    ConfigFactoryInterface $config_factory,
    PathValidatorInterface $pathValidator,
    RequestStack $requestStack
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->languageManager = $language_manager;
    $this->configFactory = $config_factory;
    $this->pathValidator = $pathValidator;
    $this->requestStack = $requestStack;
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
      $container->get('path.validator'),
      $container->get('request_stack'),
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
      '#title' => $this->t('Olapic Plp Data Instance'),
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
    $data_apikey = $this->configFactory->get('alshaya_olapic.settings')->get($data_apikey_field_name);
    $data_instance = $this->configuration[$data_instance_field_name];
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
