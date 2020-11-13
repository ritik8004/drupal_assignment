<?php

namespace Drupal\alshaya_fit_calculator\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides Fit calculator block.
 *
 * @Block(
 *   id = "alshaya_fit_calculator",
 *   admin_label = @Translation("Alshaya fit calculator")
 * )
 */
class AlshayaFitCalculatorBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Node Storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $nodeStorage;

  /**
   * Constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param string $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->nodeStorage = $entity_type_manager->getStorage('node');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    $config = $this->getConfiguration();

    $form['calculator_values'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Values'),
      '#description' => $this->t('Json array to calculate the size.'),
      '#default_value' => isset($config['calculator_values']) ? $config['calculator_values'] : '',
    ];

    // URL field for autocomplete from content type static_html.
    $form['size_conversion_html'] = [
      '#type' => 'entity_autocomplete',
      '#description' => $this->t('Static HTML page for size conversion chart.'),
      '#target_type' => 'node',
      '#selection_settings' => [
        'target_bundles' => ['static_html'],
      ],
      '#default_value' => isset($config['size_conversion_html']) ? $this->nodeStorage->load($config['size_conversion_html']) : '',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $values = $form_state->getValues();
    $this->configuration['calculator_values'] = $values['calculator_values'];
    $this->configuration['size_conversion_html'] = $values['size_conversion_html'];
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();
    // Remove newline and tabs from string.
    $sizeData = isset($config['calculator_values']) ? trim(preg_replace('/\s+/', ' ', $config['calculator_values'])) : NULL;
    $sizeConversionChartUrl = NULL;
    if (isset($config['size_conversion_html'])) {
      $url = Url::fromRoute('entity.node.canonical', ['node' => $config['size_conversion_html']]);
      $sizeConversionChartUrl = $url->toString();
    }

    return [
      '#markup' => '<div id="fit-calculator-container"></div>',
      '#attached' => [
        'library' => [
          'alshaya_fit_calculator/alshaya_fit_calculator',
        ],
        'drupalSettings' => [
          'fitCalculator' => [
            'sizeData' => $sizeData,
            'sizeConversionChartUrl' => $sizeConversionChartUrl,
          ],
        ],
      ],
    ];
  }

}
