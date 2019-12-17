<?php

namespace Drupal\alshaya_acm_product_category\Plugin\Block;

use Drupal\alshaya_acm_product_category\ProductCategoryTree;
use Drupal\alshaya_custom\Utility;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
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
      $selected_subcategories = array_column($term->get('field_select_sub_categories_plp')->getValue(), 'value');

      foreach ($selected_subcategories as $selected_subcategory) {
        $subcategory = $this->termStorage->load($selected_subcategory);

        if (!($subcategory instanceof TermInterface)) {
          continue;
        }

        // Get current language translation if available.
        if ($subcategory->hasTranslation($current_language)) {
          $subcategory = $subcategory->getTranslation($current_language);
        }

        $data = [];
        $data['tid'] = $subcategory->id();

        $data['title'] = $subcategory->get('field_plp_group_category_title')->getString();
        if (empty($data['title'])) {
          $data['title'] = $subcategory->label();
        }

        $data['weight'] = $subcategory->getWeight();
        $data['description'] = $subcategory->get('field_plp_group_category_desc')->value ?? '';

        $value = $subcategory->get('field_plp_group_category_img')->getValue()[0] ?? [];
        if (!empty($value) && ($image = $this->fileStorage->load($value['target_id'])) instanceof FileInterface) {
          $data['image']['url'] = file_url_transform_relative(file_create_url($image->getFileUri()));
          $data['image']['alt'] = $value['alt'];
        }

        $subcategories[$subcategory->id()] = $data;
      }
      uasort($subcategories, [Utility::class, 'weightArraySort']);

      return [
        '#theme' => 'alshaya_subcategory_block',
        '#subcategories' => $subcategories,
        '#attached' => [
          'library' => [
            'alshaya_acm_product_category/alshaya_subcategory_scroll',
          ],
        ],
      ];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    $tags = parent::getCacheTags();

    $term = $this->productCategoryTree->getCategoryTermFromRoute();

    if ($term instanceof TermInterface) {
      // Add current term tag always.
      $tags = Cache::mergeTags($tags, $term->getCacheTags());

      // Add selected sub category terms if grouping enabled.
      if ($term->get('field_group_by_sub_categories')->getString()) {
        $selected_subcategories = $term->get('field_select_sub_categories_plp')->getValue();
        foreach ($selected_subcategories as $selected_subcategory) {
          $tags[] = 'taxonomy_term:' . $selected_subcategory['value'];
        }
      }
    }

    return $tags;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return Cache::mergeContexts(parent::getCacheContexts(), ['url.path']);
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    // Get the term object from current route.
    $term = $this->productCategoryTree->getCategoryTermFromRoute();

    if ($term instanceof TermInterface && $term->get('field_group_by_sub_categories')) {
      if ($term->get('field_group_by_sub_categories')->getString()) {
        $cachetags = $this->getCacheTags();

        // Allowed if group by sub categories is enabled.
        return AccessResult::allowed()->addCacheTags($cachetags);
      }

      // Denied if group by sub categories is not enabled.
      // We still add current term cache tags for access check.
      return AccessResult::forbidden()->addCacheTags($term->getCacheTags());
    }
    return AccessResult::forbidden();
  }

}
