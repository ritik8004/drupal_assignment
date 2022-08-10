<?php

namespace Drupal\alshaya_search_api\Plugin\Condition;

use Drupal\Core\Condition\ConditionPluginBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides condition for "Brand LHN" feature.
 *
 * @Condition(
 *   id = "alshaya_listing_page_types",
 *   label = @Translation("Alshaya Page Types"),
 * )
 */
class AlshayaListingPageTypes extends ConditionPluginBase implements ContainerFactoryPluginInterface {

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * AlshayaListingPageTypes Constructor.
   *
   * @param array $configuration
   *   The plugin configuration, i.e. an array with configuration values keyed
   *   by configuration option name. The special key 'context' may be used to
   *   initialize the defined contexts by setting it to an array of context
   *   values keyed by context names.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory.
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
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $config = parent::defaultConfiguration();
    $page_types = static::getPageTypes();
    foreach ($page_types as $pageType => $values) {
      $config['page_types'][$pageType] = 0;
    }
    $config['show_on_selected_pages'] = 0;

    return $config;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $page_types = static::getPageTypes();
    $form['page_types'] = [
      '#title' => $this->t('Select the Page Types'),
      '#type' => 'fieldset',
    ];
    foreach ($page_types as $page_type => $page_type_value) {
      $form['page_types'][$page_type] = [
        '#type' => 'checkbox',
        '#title' => $page_type_value,
        '#default_value' => $this->configuration['page_types'][$page_type],
      ];
    }
    $form['show_on_selected_pages'] = [
      '#type' => 'radios',
      '#options' => [
        1 => $this->t('Show in the selected page types'),
        0 => $this->t('Hide in the selected page types'),
      ],
      '#default_value' => $this->configuration['show_on_selected_pages'],
    ];
    $form += parent::buildConfigurationForm($form, $form_state);
    unset($form['negate']);
    unset($form['context_mapping']);
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['page_types'] = $form_state->getValue('page_types');
    $this->configuration['show_on_selected_pages'] = $form_state->getValue('show_on_selected_pages');
    parent::submitConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   *
   * Kept empty as it is a mandatory function to implement.
   * Human readable summary is not required here.
   */
  public function summary() {
  }

  /**
   * {@inheritdoc}
   *
   *  Returning TRUE as we don't require any conditions here and
   *  it is a mandatory function to implement.
   */
  public function evaluate() {
    return TRUE;
  }

  /**
   * Custom function to get the page types.
   */
  public static function getPageTypes() {
    static $page_types = [];
    if (empty($page_types)) {
      $page_types['search'] = t('Search');
      $page_types['plp'] = t('Category Listing');
      $page_types['promotion'] = t('Promotion Listing');
      // Invoke hook to allow other modules to add new page types.
      \Drupal::moduleHandler()->alter('alshaya_search_api_listing_page_types', $page_types);
    }
    return $page_types;
  }

}
