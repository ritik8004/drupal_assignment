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
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['page_types'] = [
      '#title' => $this->t('Select the Page Types'),
      '#type' => 'fieldset',
    ];
    $form['page_types']['search'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Search page'),
      '#default_value' => $this->configuration['page_types']['search'],
    ];
    $form['page_types']['plp'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Category Listing'),
      '#default_value' => $this->configuration['page_types']['plp'],
    ];
    $form['page_types']['promotion'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Promotion Listing'),
      '#default_value' => $this->configuration['page_types']['promotion'],
    ];
    // Invoke hook to allow other modules to add new page types.
    $page_types = [];
    \Drupal::moduleHandler()->alter('alshaya_search_api_listing_page_types', $page_types);
    foreach ($page_types as $page_type => $page_type_value) {
      $form['page_types'][$page_type] = [
        '#type' => 'checkbox',
        '#title' => $page_type_value[$page_type],
        '#default_value' => $this->configuration['page_types'][$page_type],
      ];
    }
    $form['show_on_selected_pages'] = [
      '#type' => 'radios',
      '#options' => [
        1 => $this->t('Show in the selected page types'),
        0 => $this->t('Hide in the selected page types'),
      ],
      '#default_value' => $this->configuration['show_on_selected_pages'] ?: 1,
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
   *  Kept empty as it is a mandatory function to implement.
   */
  public function evaluate() {
  }

}
