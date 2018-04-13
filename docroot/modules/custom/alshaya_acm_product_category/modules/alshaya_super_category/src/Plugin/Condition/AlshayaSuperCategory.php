<?php

namespace Drupal\alshaya_super_category\Plugin\Condition;

use Drupal\alshaya_acm_product_category\ProductCategoryTree;
use Drupal\Core\Condition\ConditionPluginBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\taxonomy\TermInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides condition for "super category" feature.
 *
 * @Condition(
 *   id = "alshaya_super_category",
 *   label = @Translation("Alshaya Super Category"),
 *   context = {
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
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ProductCategoryTree $product_category_tree) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->productCategoryTree = $product_category_tree;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('alshaya_acm_product_category.product_category_tree')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $terms = $this->productCategoryTree->getCategoryRootTerms();
    // Create option array of root terms.
    foreach ($terms as $term) {
      $options[$term['id']] = $term['label'];
    }

    // Form to select root terms.
    $form['categories'] = [
      '#type' => 'checkboxes',
      '#title' => t('Categories'),
      '#description' => t('Please select categories for which you want to show this block.'),
      '#options' => $options,
      '#default_value' => $this->configuration['categories'],
    ];
    return parent::buildConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['categories'] = $form_state->getValue('categories');
    parent::submitConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return ['categories' => []] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function summary() {
    if (count($this->configuration['categories']) > 1) {
      $terms = $this->configuration['categories'];
      $last = array_pop($terms);
      $terms = implode(', ', $terms);
      return $this->t('The taxonomy term is @bundles or @last', ['@bundles' => $terms, '@last' => $last]);
    }
    $category = reset($this->configuration['categories']);

    return $this->t('The taxonomy term is @bundle', ['@bundle' => $category]);
  }

  /**
   * {@inheritdoc}
   */
  public function evaluate() {
    $categories = array_filter($this->configuration['categories']);
    if (empty($categories) && !$this->isNegated()) {
      return TRUE;
    }
    // @todo: check why this context is not working in block.
    // $term = $this->getContextValue('taxonomy_term');
    $term = $this->productCategoryTree->getCategoryTermFromRoute();
    $parent = $this->productCategoryTree->getCategoryTermRootParent($term);
    if ($parent instanceof TermInterface) {
      return in_array($parent->id(), $this->configuration['categories']);
    }

    return TRUE;
  }

}
