<?php

namespace Drupal\alshaya_fit_calculator\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

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
   * Term Storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $termStorage;

  /**
   * Request Stack service.
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
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              RequestStack $request_stack,
                              EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->requestStack = $request_stack;
    $this->nodeStorage = $entity_type_manager->getStorage('node');
    $this->termStorage = $entity_type_manager->getStorage('taxonomy_term');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('request_stack'),
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
      '#default_value' => $config['calculator_values'] ?? '',
    ];

    // URL field for autocomplete from content type static_html.
    $form['size_conversion_html'] = [
      '#type' => 'entity_autocomplete',
      '#title' => $this->t('Size conversion chart page.'),
      '#description' => $this->t('Static HTML page for size conversion chart.'),
      '#target_type' => 'node',
      '#selection_settings' => [
        'target_bundles' => ['static_html'],
      ],
      '#default_value' => isset($config['size_conversion_html']) ? $this->nodeStorage->load($config['size_conversion_html']) : '',
    ];

    $form['measurement_field'] = [
      '#type' => 'select',
      '#title' => $this->t('Fit calculaor for Advanced page or size guide modal.'),
      '#description' => $this->t('Fit calculator is for advanced page or size guide modal.'),
      '#options' => [
        'main-form' => $this->t('Advanced-page'),
        'size-guide-calculator' => $this->t('Size guide modal'),
      ],
      '#default_value' => $config['measurement_field'] ?? 'main-form',
    ];

    $form['plp_page'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Provide PLP page.'),
      '#description' => $this->t('PLP page for link in calculator result with filter. eg: /victorias-secret/shop-bras/all-bras/'),
      '#default_value' => $config['plp_page'] ?? '',
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
    $this->configuration['measurement_field'] = $values['measurement_field'];
    $this->configuration['plp_page'] = $values['plp_page'];
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();
    // Remove newline and tabs from string.
    $sizeData = isset($config['calculator_values']) ? trim(preg_replace('/\s+/', ' ', $config['calculator_values'])) : NULL;
    $sizeConversionChartUrl = NULL;
    if (isset($config['size_conversion_html']) && !empty($config['size_conversion_html'])) {
      $url = Url::fromRoute('alshaya_fit_calculator.modal_links', ['node' => $config['size_conversion_html']]);
      $sizeConversionChartUrl = $url->toString();
    }
    $plp_page = '';
    if ($config['plp_page']) {
      $plp_page = trim($config['plp_page'], '/') . '/';
    }

    if (strstr($this->requestStack->getCurrentRequest()->getRequestUri(), 'modal-link-views')) {
      $markup = '<div id="fit-cal-modal"></div>';
    }
    else {
      $markup = '<div id="fit-calculator-container"></div>';
    }

    return [
      '#markup' => $markup,
      '#attached' => [
        'library' => [
          'alshaya_fit_calculator/alshaya_fit_calculator',
          'alshaya_white_label/alshaya-fit-calculator',
        ],
        'drupalSettings' => [
          'fitCalculator' => [
            'sizeData' => $sizeData,
            'sizeConversionChartUrl' => $sizeConversionChartUrl,
            'measurementField' => $config['measurement_field'] ?? 'main-form',
            'plpPage' => $plp_page,
          ],
        ],
      ],
    ];
  }

}
