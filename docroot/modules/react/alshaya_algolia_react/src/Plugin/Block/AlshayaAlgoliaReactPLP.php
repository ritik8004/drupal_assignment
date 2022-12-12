<?php

namespace Drupal\alshaya_algolia_react\Plugin\Block;

use Drupal\alshaya_acm_product_category\Service\ProductCategoryPage;
use Drupal\alshaya_algolia_react\AlshayaAlgoliaReactBlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\taxonomy\TermInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\alshaya_algolia_react\Services\AlshayaAlgoliaReactConfigInterface;
use Drupal\alshaya_acm_product_category\ProductCategoryTree;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\file\FileInterface;
use Drupal\alshaya_custom\Utility;
use Drupal\alshaya_search_api\AlshayaSearchApiHelper;

/**
 * Provides a block to display 'plp' results.
 *
 * @Block(
 *   id = "alshaya_algolia_react_plp",
 *   admin_label = @Translation("Alshaya Algolia React PLP")
 * )
 */
class AlshayaAlgoliaReactPLP extends AlshayaAlgoliaReactBlockBase {

  public const PAGE_TYPE = 'listing';
  public const PAGE_SUB_TYPE = 'plp';

  /**
   * Entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Product category page service.
   *
   * @var \Drupal\alshaya_acm_product_category\Service\ProductCategoryPage
   */
  protected $productCategoryPage;

  /**
   * The Alshaya Algolia React Config.
   *
   * @var \Drupal\alshaya_algolia_react\Services\AlshayaAlgoliaReactConfigInterface
   */
  protected $alshayaAlgoliaReactConfig;

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
   * Entity Repository service.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * AlshayaAlgoliaReactAutocomplete constructor.
   *
   * @param array $configuration
   *   Configuration array.
   * @param string $plugin_id
   *   Plugin id.
   * @param mixed $plugin_definition
   *   Plugin definition.
   * @param \Drupal\alshaya_acm_product_category\Service\ProductCategoryPage $product_category_page
   *   Product category page service.
   * @param \Drupal\alshaya_algolia_react\Services\AlshayaAlgoliaReactConfigInterface $alshaya_algolia_react_config
   *   Alshaya Algolia React Config.
   * @param \Drupal\alshaya_acm_product_category\ProductCategoryTree $product_category_tree
   *   Product category tree.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entityRepository
   *   Entity Repository service.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    ProductCategoryPage $product_category_page,
    AlshayaAlgoliaReactConfigInterface $alshaya_algolia_react_config,
    ProductCategoryTree $product_category_tree,
    EntityTypeManagerInterface $entity_type_manager,
    EntityRepositoryInterface $entityRepository
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->productCategoryPage = $product_category_page;
    $this->alshayaAlgoliaReactConfig = $alshaya_algolia_react_config;
    $this->productCategoryTree = $product_category_tree;
    $this->termStorage = $entity_type_manager->getStorage('taxonomy_term');
    $this->fileStorage = $entity_type_manager->getStorage('file');
    $this->entityRepository = $entityRepository;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition
  ) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('alshaya_acm_product_category.page'),
      $container->get('alshaya_algoila_react.alshaya_algolia_react_config'),
      $container->get('alshaya_acm_product_category.product_category_tree'),
      $container->get('entity_type.manager'),
      $container->get('entity.repository')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Get common configuration for Algolia pages.
    $common_config = $this->alshayaAlgoliaReactConfig->getAlgoliaReactCommonConfig(self::PAGE_TYPE, self::PAGE_SUB_TYPE);

    // Get common config and merge with new array.
    $filters = $common_config[self::PAGE_TYPE]['filters'];
    $lang = $common_config['otherRequiredValues']['lang'];

    $algoliaSearchValues = [
      'local_storage_expire' => $common_config['otherRequiredValues']['local_storage_expire'],
      'filters_alias' => array_column($filters, 'identifier', 'alias'),
      'max_category_tree_depth' => $common_config['otherRequiredValues']['max_category_tree_depth'],
    ];

    $algoliaSearchValues = array_merge($algoliaSearchValues, $this->productCategoryPage->getCurrentSelectedCategory($lang));
    // Set default EN category filter in product list index for VM.
    if (AlshayaSearchApiHelper::isIndexEnabled('alshaya_algolia_product_list_index')) {
      $algoliaSearchValues['hierarchy_lhn'] = $algoliaSearchValues['hierarchy'];
      $algoliaSearchValues = array_merge($algoliaSearchValues, $this->productCategoryPage->getCurrentSelectedCategory('en'));
    }
    $reactTeaserView = $common_config['commonReactTeaserView'];
    $commonAlgoliaSearchValues = $common_config['commonAlgoliaSearch'];
    $algoliaSearch = array_merge($commonAlgoliaSearchValues, $algoliaSearchValues);
    $algoliaSearch[self::PAGE_TYPE] = $common_config[self::PAGE_TYPE];
    $algoliaSearch['pageSubType'] = self::PAGE_SUB_TYPE;

    // Get sub categories information.
    $term = $this->productCategoryTree->getCategoryTermFromRoute();
    if ($term instanceof TermInterface) {
      $term = $this->entityRepository->getTranslationFromContext($term);

      // We need to show Category facet only for the Categories which are
      // visible in menu. Condition here is same as what we use to populate
      // lhn_category field in Algolia Index.
      // @see AlshayaAlgoliaIndexHelper::getCategoryHierarchy()
      $algoliaSearch['categoryFacetEnabled'] = $term->hasField('field_category_include_menu')
        ? (int) $term->get('field_category_include_menu')->getString()
        : 0;

      $group_sub_category_enabled = $term->hasField('field_group_by_sub_categories')
        ? $term->get('field_group_by_sub_categories')->getString()
        : FALSE;
      if ($group_sub_category_enabled) {
        $sub_categories = $term->get('field_select_sub_categories_plp')->getValue();
        $subcategories = [];
        foreach ($sub_categories as $term_id) {
          $subcategory = $this->termStorage->load($term_id['value']);
          if ($subcategory instanceof TermInterface) {
            $subcategory = $this->entityRepository->getTranslationFromContext($subcategory);

            $data = [];
            $data['tid'] = $subcategory->id();

            $data['title'] = $subcategory->get('field_plp_group_category_title')->getString();
            if (empty($data['title'])) {
              $data['title'] = $subcategory->label();
            }

            $data['weight'] = $subcategory->getWeight();
            $data['description'] = $subcategory->get('field_plp_group_category_desc')->getValue()[0]['value'] ?? '';

            $value = $subcategory->get('field_plp_group_category_img')->getValue()[0] ?? [];
            $image = (!empty($value)) ? $this->fileStorage->load($value['target_id']) : NULL;
            if ($image instanceof FileInterface) {
              $data['image']['url'] = file_url_transform_relative(file_create_url($image->getFileUri()));
              $data['image']['alt'] = $value['alt'];
            }

            // Get category level informartion.
            // We use only EN lang-code for the category filters.
            $data['category'] = $this->productCategoryPage->getCurrentSelectedCategory('en', $subcategory->id());

            $subcategories[$subcategory->id()] = $data;
          }
        }
        uasort($subcategories, [Utility::class, 'weightArraySort']);

        $algoliaSearch['subCategories'] = $subcategories;
      }
    }

    return [
      'inside' => [
        '#type' => 'html_tag',
        '#tag' => 'div',
        '#attributes' => [
          'id' => 'alshaya-algolia-plp',
          'data-hierarchy' => $algoliaSearch['hierarchy'] ?? '',
          'data-level' => $algoliaSearch['level'] ?? '',
          'data-rule-context' => $algoliaSearch['ruleContext'] ? implode(',', $algoliaSearch['ruleContext']) : '',
          'data-category-field' => $algoliaSearch['category_field'] ?? '',
        ],
        '#attached' => [
          'library' => $common_config['otherRequiredValues']['libraries'],
          'drupalSettings' => [
            'algoliaSearch' => $algoliaSearch,
            'reactTeaserView' => $reactTeaserView,
          ],
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    $tags = ['alshaya_acm_product_position.settings'];
    $tags = Cache::mergeTags(parent::getCacheTags(), $tags);

    $term = $this->productCategoryTree->getCategoryTermFromRoute();
    if ($term instanceof TermInterface) {
      $tags = Cache::mergeTags($term->getCacheTags(), $tags);
    }

    return $tags;
  }

}
