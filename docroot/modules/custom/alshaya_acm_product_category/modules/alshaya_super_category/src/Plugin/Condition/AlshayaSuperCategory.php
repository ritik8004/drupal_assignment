<?php

namespace Drupal\alshaya_super_category\Plugin\Condition;

use Drupal\alshaya_acm_product_category\ProductCategoryTree;
use Drupal\Core\Condition\ConditionPluginBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides condition for "super category" feature.
 *
 * @Condition(
 *   id = "alshaya_super_category",
 *   label = @Translation("Alshaya Super Category"),
 *   context_definitions = {
 *     "term" = @ContextDefinition("entity:taxonomy_term", label = @Translation("Taxonomy term"), required = FALSE),
 *   }
 * )
 */
class AlshayaSuperCategory extends ConditionPluginBase implements ContainerFactoryPluginInterface {

  /**
   * Product category tree.
   *
   * @var \Drupal\alshaya_acm_product_category\ProductCategoryTree
   */
  protected $productCategoryTree;

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
   * @param \Drupal\alshaya_acm_product_category\ProductCategoryTree $product_category_tree
   *   Product category tree.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ProductCategoryTree $product_category_tree, ConfigFactoryInterface $config_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->productCategoryTree = $product_category_tree;
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
      $container->get('alshaya_acm_product_category.product_category_tree'),
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $terms = $this->productCategoryTree->getCategoryRootTerms();
    $options = [];
    // Create option array of root terms.
    foreach ($terms as $term) {
      $options[$term['id']] = $term['label'];
    }

    $super_category_status = $this->configFactory->get('alshaya_super_category.settings')->get('status');
    $form['use_super_category'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Use super-category context'),
      '#default_value' => $this->configuration['use_super_category'],
      '#disabled' => !$super_category_status,
    ];

    if (!$super_category_status) {
      $form['use_super_category']['#description'] = $this->t('Note: Enable super category feature to use this condition.');
    }

    // Form to select root terms.
    $form['categories'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Categories'),
      '#description' => $this->t('Please select categories for which you want to show this block.'),
      '#options' => $options,
      '#default_value' => $this->configuration['categories'] ?: array_keys($options),
      '#states' => [
        'visible' => [
          ':input[name="visibility[alshaya_super_category][use_super_category]"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $form += parent::buildConfigurationForm($form, $form_state);
    $form['negate']['#states'] = [
      'visible' => [
        ':input[name="visibility[alshaya_super_category][use_super_category]"]' => ['checked' => TRUE],
      ],
    ];
    unset($form['context_mapping']);
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['use_super_category'] = $form_state->getValue('use_super_category');
    $this->configuration['categories'] = $form_state->getValue('categories');
    parent::submitConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'use_super_category' => FALSE,
      'categories' => [],
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function summary() {
    if ((is_countable($this->configuration['categories']) ? count($this->configuration['categories']) : 0) > 1) {
      return $this->t('There are multiple categories selected');
    }
    $category = reset($this->configuration['categories']);
    return $this->t('The category term is @bundle', ['@bundle' => $category]);
  }

  /**
   * {@inheritdoc}
   */
  public function evaluate() {
    $categories = array_filter($this->configuration['categories']);
    if ((empty($categories) && !$this->isNegated()) || !$this->configuration['use_super_category']) {
      return TRUE;
    }

    // @todo check why this context is not working in block.
    // $term = $this->getContextValue('taxonomy_term');
    $parent = $this->productCategoryTree->getCategoryTermRequired();
    if ((is_countable($parent) ? count($parent) : 0) > 0) {
      return in_array($parent['id'], $this->configuration['categories']);
    }
    return FALSE;
  }

}
