<?php

namespace Drupal\alshaya_search_api\Plugin\Condition;

use Drupal\Core\Condition\ConditionPluginBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides condition for "super category" feature.
 *
 * @Condition(
 *   id = "alshaya_listing_page_types",
 *   label = @Translation("Page Types"),
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
   * Creates a new Webform instance.
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
      '#title' => 'Select the Page Types',
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
    $form['page_types']['product_option_list'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Product Option Listing'),
      '#default_value' => $this->configuration['page_types']['product_option_list'],
    ];
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
    $this->configuration['page_types']['search'] = $form_state->getValue('page_types')['search'];
    $this->configuration['page_types']['plp'] = $form_state->getValue('page_types')['plp'];
    $this->configuration['page_types']['promotion'] = $form_state->getValue('page_types')['promotion'];
    $this->configuration['page_types']['product_option_list'] = $form_state->getValue('page_types')['product_option_list'];
    $this->configuration['show_on_selected_pages'] = $form_state->getValue('show_on_selected_pages');
    parent::submitConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'page_types' => [
        'search' => 0,
        'plp' => 0,
        'promotion' => 0,
        'product_option_list' => 1,
      ],
      'show_on_selected_pages' => 1,
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function summary() {
  }

  /**
   * {@inheritdoc}
   */
  public function evaluate() {
  }

}
