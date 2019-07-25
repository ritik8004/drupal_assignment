<?php

namespace Drupal\alshaya_acm_product_category\Plugin\Block;

use Drupal\alshaya_acm_product_category\ProductCategoryTree;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\file\FileInterface;
use Drupal\taxonomy\TermInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;

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
   * Language Manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

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
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   Language Manager.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ProductCategoryTree $product_category_tree, EntityTypeManagerInterface $entity_type_manager, LanguageManagerInterface $language_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->productCategoryTree = $product_category_tree;
    $this->termStorage = $entity_type_manager->getStorage('taxonomy_term');
    $this->fileStorage = $entity_type_manager->getStorage('file');
    $this->languageManager = $language_manager;
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
      $container->get('entity_type.manager'),
      $container->get('language_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $subcategories = [];
    // Get the term object from current route.
    $term = $this->productCategoryTree->getCategoryTermFromRoute();
    $current_language = $this->languageManager->getCurrentLanguage()->getId();

    if ($term instanceof TermInterface) {
      // Get all selected subcategories to be displayed on PLP.
      $selected_subcategories = $term->get('field_select_subcategories_plp')->getValue();

      foreach ($selected_subcategories as $selected_subcategory) {
        $subcategory = $this->termStorage->load($selected_subcategory['value']);
        // Get current language translation if available.
        if ($subcategory->hasTranslation($current_language)) {
          $subcategory = $subcategory->getTranslation($current_language);
        }
        if ($subcategory instanceof TermInterface) {
          $subcategories[$subcategory->id()]['title'] = $subcategory->get('field_plp_group_category_title')->value ?? $subcategory->label();
          $subcategories[$subcategory->id()]['tid'] = $subcategory->id();
          if ($subcategory->get('field_plp_group_category_img')->first()) {
            $file_value = $subcategory->get('field_plp_group_category_img')->first()->getValue();
            $image = $this->fileStorage->load($file_value['target_id']);
            if ($image instanceof FileInterface) {
              $subcategories[$subcategory->id()]['image'] = file_url_transform_relative(file_create_url($image->getFileUri()));
            }
          }
          $subcategories[$subcategory->id()]['description'] = $subcategory->get('field_plp_group_category_desc')->value;
        }
      }

      return [
        '#theme' => 'alshaya_subcategory_block',
        '#subcategories' => $subcategories,
      ];
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    // Get the term object from current route.
    $term = $this->productCategoryTree->getCategoryTermFromRoute();
    if ($term instanceof TermInterface && $term->get('field_group_by_sub_category')) {
      $cachetags[] = 'taxonomy_term:' . $term->id();
      if ($term->get('field_group_by_sub_category')->value) {
        $selected_subcategories = $term->get('field_select_subcategories_plp')->getValue();
        foreach ($selected_subcategories as $selected_subcategory) {
          $cachetags[] = 'taxonomy_term:' . $selected_subcategory->id();
        }
        return AccessResult::allowed()->addCacheTags($cachetags);
      }
    }
    return AccessResult::forbidden()->addCacheTags(['taxonomy_term:' . $term->id()]);
  }

}
