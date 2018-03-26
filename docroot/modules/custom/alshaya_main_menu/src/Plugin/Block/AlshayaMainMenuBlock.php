<?php

namespace Drupal\alshaya_main_menu\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Cache\Cache;
use Drupal\alshaya_main_menu\ProductCategoryTree;
use Drupal\taxonomy\TermInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides alshaya main menu block.
 *
 * @Block(
 *   id = "alshaya_main_menu",
 *   admin_label = @Translation("Alshaya main menu")
 * )
 */
class AlshayaMainMenuBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Term storage object.
   *
   * @var \Drupal\taxonomy\TermStorageInterface
   */
  protected $termStorage;

  /**
   * Vocabulary processed data.
   *
   * @var array
   */
  protected $termData = [];

  /**
   * Array of terms for cache bubbling up.
   *
   * @var array
   */
  protected $cacheTags = [];

  /**
   * Product category tree.
   *
   * @var \Drupal\alshaya_main_menu\ProductCategoryTree
   */
  protected $productCateoryTree;

  /**
   * AlshayaMegaMenuBlock constructor.
   *
   * @param array $configuration
   *   The configuration.
   * @param string $plugin_id
   *   Plugin id.
   * @param mixed $plugin_definition
   *   Plugin defination.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager
   *   The entity type manager.
   * @param \Drupal\alshaya_main_menu\ProductCategoryTree $product_category_tree
   *   Product category tree.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_manager, ProductCategoryTree $product_category_tree) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->termStorage = $entity_manager->getStorage('taxonomy_term');
    $this->productCateoryTree = $product_category_tree;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('alshaya_main_menu.product_category_tree')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'follow_category_term' => 0,
      'default_parent' => NULL,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $defaults = $this->defaultConfiguration();

    $form['menu_config'] = [
      '#type' => 'details',
      '#title' => $this->t('Menu Config'),
      // Open if not set to defaults.
      '#open' => $defaults['top_level_category'] !== $this->configuration['top_level_category'] || $defaults['follow_category_term'] !== $this->configuration['follow_category_term'],
      '#process' => [[get_class(), 'processMenuConfigParents']],
    ];

    $form['menu_config']['follow_category_term'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Display second level menu item as per current path.'),
      '#default_value' => $this->configuration['follow_category_term'] ? $this->configuration['follow_category_term'] : $defaults['follow_category_term'],
    ];

    $form['menu_config']['default_parent'] = [
      '#type' => 'select',
      '#title' => $this->t('Default Parent.'),
      '#options' => $this->getSelectListTopCategoryTerms(),
      '#default_value' => empty($this->configuration['default_parent']) ?: $this->configuration['default_parent'],
      '#states' => [
        'visible' => [
          ':input[name="settings[follow_category_term]"]' => ['checked' => TRUE],
        ],
      ],
    ];

    return $form;
  }

  /**
   * Create option array of top level category for select list.
   *
   * @return array
   *   Return array of top level category terms.
   */
  private function getSelectListTopCategoryTerms() {
    $options = ['' => $this->t('-Select-')];
    $terms = $this->productCateoryTree->getTopLevelCategory();

    foreach ($terms as $term => $term_data) {
      $options[$term] = $term_data['label'];
    }
    return $options;
  }

  /**
   * Adjusts the #parents of menu_config to save its children at the top level.
   */
  public static function processMenuConfigParents(&$element, FormStateInterface $form_state, &$complete_form) {
    array_pop($element['#parents']);
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['follow_category_term'] = (int) $form_state->getValue('follow_category_term');
    if ($form_state->getValue('default_parent')) {
      $this->configuration['default_parent'] = (int) $form_state->getValue('default_parent');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Get the term object from current route.
    $term = $this->productCateoryTree->getCategoryTermFromRoute();

    // Get the term id from the current path, and display only the related
    // second level child terms.
    if ($this->configuration['follow_category_term'] && !empty($this->configuration['default_parent'])) {
      // If term is of 'acq_product_category' vocabulary.
      if ($term instanceof TermInterface && $parents = $this->productCateoryTree->getCategoryTermParents($term)) {
        // Get the top level parent id if parent exists.
        $parents = array_keys($parents);
        $parent_id = empty($parents) ? $term->id() : end($parents);
      }
      // Set the default parent term to display menu on other pages.
      else {
        $parent_id = $this->configuration['default_parent'];
      }
      // Child terms of given parent term id.
      $term_data = $this->productCateoryTree->getCategoryTreeCached($parent_id);
    }
    // Default category terms.
    else {
      $term_data = $this->productCateoryTree->getCategoryTreeCached();
    }

    // If no data, no need to render the block.
    if (empty($term_data)) {
      return [];
    }

    // Get all parents of the given term.
    if ($term instanceof TermInterface) {
      $parents = $this->productCateoryTree->getCategoryTermParents($term);

      if (!empty($parents)) {
        /* @var \Drupal\taxonomy\TermInterface $root_parent_term */
        foreach ($parents as $parent) {
          if (isset($term_data[$parent->id()])) {
            $term_data[$parent->id()]['class'] = 'active';
          }
        }
      }
    }

    return [
      '#theme' => 'alshaya_main_menu_level1',
      '#term_tree' => $term_data,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    // Add department page node type cache tag.
    // This is custom cache tag and cleared in hook_presave in department
    // module.
    $this->cacheTags[] = 'node_type:department_page';

    // Discard cache for the block once a term gets updated.
    $this->cacheTags[] = ProductCategoryTree::VOCABULARY_ID;

    return Cache::mergeTags(
      parent::getCacheTags(),
      $this->cacheTags
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return Cache::mergeContexts(parent::getCacheContexts(), ['url.path']);
  }

}
