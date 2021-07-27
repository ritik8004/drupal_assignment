<?php

namespace Drupal\rcs_placeholders\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\views\Views;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a basic block for product list.
 *
 * @Block(
 *   id = "rcs_ph_products_list",
 *   admin_label = @Translation("RCS Plaeholders product list"),
 *   category = @Translation("RCS Placeholders")
 * )
 */
class RcsPhProductListBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Logger.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, LoggerInterface $logger) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->logger = $logger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('logger.factory')->get('RcsPhProductList')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $current_configuration = $this->getConfiguration();

    // @todo List down available block IDs from a hook.
    $form['block_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Block ID'),
      '#description' => $this->t('The block ID is used by the frontend to determine which data to fetch and how to render these.'),
      '#default_value' => $current_configuration['id'] ?? '',
      '#required' => TRUE,
      '#attributes' => [
        'placeholder' => $this->t('rcs-ph-related_products'),
      ],
    ];

    // @todo list down available views for RCS Product.
    $form['view_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Drupal View ID'),
      '#description' => $this->t('Drupal View ID to use to render the content.'),
      '#default_value' => $current_configuration['view_id'] ?? '',
      '#required' => TRUE,
    ];

    // @todo dynamically list down the available displays for the selected view.
    $form['view_display'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Drupal View Display'),
      '#description' => $this->t('Drupal View Display to use to render the content.'),
      '#default_value' => $current_configuration['view_display'] ?? '',
      '#required' => TRUE,
    ];

    $form['number_of_products'] = [
      '#type' => 'number',
      '#min' => 1,
      '#max' => 12,
      '#title' => $this->t('Number of products to display'),
      '#description' => $this->t('Enter the number of products to display.'),
      '#default_value' => $current_configuration['number_of_products'] ?? 4,
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    try {
      $view = Views::getView($form_state->getValue('view_id'));
      $view->setDisplay($form_state->getValue('view_display'));

      if ($view->current_display !== $form_state->getValue('view_display')) {
        throw new \Exception('Unable to load view display');
      }
    }
    catch (\Throwable $e) {
      $form_state->setErrorByName('view_id', 'Unable to load view or view display, please check and try again.');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);
    $this->configuration['block_id'] = $form_state->getValue('block_id');
    $this->configuration['view_id'] = $form_state->getValue('view_id');
    $this->configuration['view_display'] = $form_state->getValue('view_display');
    $this->configuration['number_of_products'] = $form_state->getValue('number_of_products');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();

    try {
      $view = Views::getView($config['view_id']);
      $view->setDisplay($config['view_display']);

      if ($view->current_display !== $config['view_display']) {
        throw new \Exception('Unable to load view display');
      }
    }
    catch (\Throwable $e) {
      $this->logger->warning('Error occurred while trying to load view @view or view display @display. Exception: @message.', [
        '@view' => $config['view_id'],
        '@display' => $config['view_display'],
        '@message' => $e->getMessage(),
      ]);

      return [];
    }

    $build = [];

    $build['wrapper'] = [
      '#type' => 'container',
      '#attributes' => [
        // @todo instead of id we should use data param to avoid having issues
        // with divs with same ID multiple times.
        'id' => $config['block_id'],
        'data-param-limit' => $config['number_of_products'],
      ],
    ];

    $build['wrapper']['content'] = views_embed_view(
      $config['view_id'],
      $config['view_display']
    );

    $build['wrapper']['end'] = [
      '#type' => 'markup',
      '#markup' => '<div class="rcs-end-ph"></div>',
    ];

    return $build;
  }

}
