<?php

namespace Drupal\alshaya_acm_product_category\Plugin\Block;

use Drupal\alshaya_acm_product_category\ProductCategoryTree;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\file\FileInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Provides Shop by block.
 *
 * @Block(
 *   id = "alshaya_sub_category_block",
 *   admin_label = @Translation("Sub Category Block"),
 * )
 */
class AlshayaSubCategoryBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Product category tree.
   *
   * @var \Drupal\alshaya_acm_product_category\ProductCategoryTree
   */
  protected $productCategoryTree;

  /**
   * Term storage object.
   *
   * @var \Drupal\taxonomy\TermStorageInterface
   */
  protected $termStorage;

  /**
   * File Storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $fileStorage;

  /**
   * AlshayaSubCategoryBlock constructor.
   *
   * @param array $configuration
   *   The configuration.
   * @param string $plugin_id
   *   Plugin id.
   * @param mixed $plugin_definition
   *   Plugin definition.
   * @param \Drupal\alshaya_acm_product_category\ProductCategoryTree $product_category_tree
   *   Product category tree.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ProductCategoryTree $product_category_tree, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->productCategoryTree = $product_category_tree;
    $this->termStorage = $entity_type_manager->getStorage('taxonomy_term');
    $this->fileStorage = $entity_type_manager->getStorage('file');
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
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $subcategories = [];
    // Get the term object from current route.
    $term = $this->productCategoryTree->getCategoryTermFromRoute();

    // Get all selected subcategories to be displayed on PLP.
    $selected_subcategories = $term->get('field_select_subcategories_plp')->getValue();

    foreach ($selected_subcategories as $selected_subcategory) {
      $subcategory = $this->termStorage->load($selected_subcategory['value']);
      $subcategories[$subcategory->id()]['title'] = $subcategory->label();
      if ($subcategory->get('field_sub_category_image')->first()) {
        $file_value = $subcategory->get('field_sub_category_image')->first()->getValue();
        $image = $this->fileStorage->load($file_value['target_id']);
        if ($image instanceof FileInterface) {
          $subcategories[$subcategory->id()]['image'] = file_url_transform_relative(file_create_url($image->getFileUri()));
        }
      }
      $subcategories[$subcategory->id()]['description'] = $subcategory->get('field_sub_category_title')->value;
    }

    return [
      '#theme' => 'alshaya_subcategory_block',
      '#subcategories' => $subcategories,
    ];
  }

}
