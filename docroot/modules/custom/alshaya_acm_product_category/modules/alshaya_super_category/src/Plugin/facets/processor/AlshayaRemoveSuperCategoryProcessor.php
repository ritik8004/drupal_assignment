<?php

namespace Drupal\alshaya_super_category\Plugin\facets\processor;

use Drupal\facets\FacetInterface;
use Drupal\facets\Processor\BuildProcessorInterface;
use Drupal\facets\Processor\ProcessorPluginBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\alshaya_acm_product_category\ProductCategoryTree;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Removes super category terms from facet items.
 *
 * @FacetsProcessor(
 *   id = "alshaya_remove_supercategory_term",
 *   label = @Translation("Alshaya remove the super category term"),
 *   description = @Translation("Removes the super category term from the facet items."),
 *   stages = {
 *     "build" = 50
 *   }
 * )
 */
class AlshayaRemoveSuperCategoryProcessor extends ProcessorPluginBase implements BuildProcessorInterface, ContainerFactoryPluginInterface {

  /**
   * Category tree.
   *
   * @var \Drupal\alshaya_acm_product_category\ProductCategoryTree
   */
  protected $categoryTree;

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * AlshayaRemoveSuperCategoryProcessor constructor.
   *
   * @param array $configuration
   *   Configuration array.
   * @param string $plugin_id
   *   Plugin id.
   * @param mixed $plugin_definition
   *   Plugin defination.
   * @param \Drupal\alshaya_acm_product_category\ProductCategoryTree $category_tree
   *   Category tree.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ProductCategoryTree $category_tree, ConfigFactoryInterface $config_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->categoryTree = $category_tree;
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
  public function build(FacetInterface $facet, array $results) {
    if (!empty($results) && $this->removeSuperCategory()) {
      // Ids of super category terms (all top level terms).
      if (!empty($super_categories = array_keys($this->categoryTree->getCategoryRootTerms()))) {
        foreach ($results as $key => $result) {
          // If super category term exists in facet item, unset it.
          if (in_array($result->getRawValue(), $super_categories)) {
            unset($results[$key]);
          }
        }
      }
    }

    // Return the results.
    return $results;
  }

  /**
   * Determines whether to remove super category term.
   *
   * @return bool
   *   Remove super category term or not.
   */
  protected function removeSuperCategory() {
    if ($this->configFactory->get('alshaya_super_category.settings')->get('status')) {
      return TRUE;
    }
    return FALSE;
  }

}
