<?php

namespace Drupal\alshaya_olapic\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Routing\RouteMatchInterface;

/**
 * Provides a 'Olapic Block' Block.
 *
 * @Block(
 *   id = "olapicblock",
 *   admin_label = @Translation("Alshaya Olapic Widget"),
 *   category = @Translation("Alshaya Olapic Widget"),
 * )
 */
class OlapicBlock extends BlockBase implements ContainerFactoryPluginInterface {


  /**
   * The language manager.
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
   * Route match service.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * Constructs an LanguageBlock object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   Route match service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, LanguageManagerInterface $language_manager, ConfigFactoryInterface $config_factory, RouteMatchInterface $route_match) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->languageManager = $language_manager;
    $this->configFactory = $config_factory;
    $this->routeMatch = $route_match;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('language_manager'),
      $container->get('config.factory'),
      $container->get('current_route_match')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'olapic_widget_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['olapic_widget_settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);
    $lang = $this->languageManager->getCurrentLanguage()->getId();
    $data_instance_field_name = 'instance_id_' . $lang;
    $form['#tree'] = TRUE;
    $form['olapic_settings'] = [
      '#type' => 'details',
      '#title' => $this->t('Olapic settings'),
      '#open' => FALSE,
    ];
    $form['olapic_settings'][$data_instance_field_name] = [
      '#type' => 'textfield',
      '#title' => $this->t('Instance Id For Current Language'),
      '#description' => $this->t('Enter your Widget Instance ID in this field (can be found in the “Widget Instances” tab in the Olapic Admin)'),
      '#default_value' => $this->configuration[$data_instance_field_name] ?? '',
    ];
    $form['olapic_settings']['div_id'] = [
      '#type' => 'textfield',
      '#title' => 'Div Id',
      '#description' => $this->t('Enter the id of the div that the SDK should be injecting the content'),
      '#default_value' => $this->configuration['div_id'] ?? '1',
    ];
    $form['olapic_settings']['dynamic_product_id'] = [
      '#type' => 'textfield',
      '#title' => 'Dynamic Product Id',
      '#description' => $this->t('Enter the id of the dynamic product id that the SDK should be injecting the content'),
      '#default_value' => $this->configuration['olapic_div_id'] ?? '',
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $values = $form_state->getValues();
    $lang = $this->languageManager->getCurrentLanguage()->getId();
    $data_instance_field_name = 'instance_id_' . $lang;
    $this->configuration[$data_instance_field_name] = $values['olapic_settings'][$data_instance_field_name];
    $this->configuration['div_id'] = $values['olapic_settings']['div_id'];
    $this->configuration['dynamic_product_id'] = $values['olapic_settings']['dynamic_product_id'];
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $env = alshaya_get_site_environment();
    $development_mode = $this->configFactory->get('alshaya_olapic.settings')->get('development_mode') ?? '';
    // Not allowing olapic block
    // For non prod environment + development mode disabled.
    if ($env != 'prod' && $development_mode == 0) {
      return [];
    }
    $routename = $this->routeMatch->getRouteName();
    $node = $this->routeMatch->getParameter('node');
    if ($routename == 'entity.node.canonical' && $node->bundle() == 'advanced_page' && $node->get('field_use_as_department_page')->value == 0) {
      return [];
    }
    $lang = $this->languageManager->getCurrentLanguage()->getId();
    $country_code = strtoupper(_alshaya_custom_get_site_level_country_code());
    $data_apikey_field_name = 'olapic_' . $lang . '_data_apikey';
    $data_instance_field_name = 'instance_id_' . $lang;
    $data_apikey = $this->configFactory->get('alshaya_olapic.settings')->get($data_apikey_field_name) ?? '';
    $data_instance = $this->configuration[$data_instance_field_name] ?? '';
    $data_lang = $lang . '_' . $country_code;
    $olapic_keys = [
      'data_apikey' => $data_apikey,
      'development_mode' => $development_mode,
      'lang' => $data_lang,
    ];
    return [
      '#theme' => 'olapic_widget',
      '#instance_id' => $data_instance,
      '#div_id' => $this->configuration['div_id'] ?? '',
      '#dynamic_product_id' => $this->configuration['dynamic_product_id'] ?? '',
      '#attached' => [
        'library' => 'alshaya_olapic/alshaya_olapic_widget',
        'drupalSettings' => [
          'olapic_keys' => $olapic_keys,
        ],
      ],
    ];
  }

}
