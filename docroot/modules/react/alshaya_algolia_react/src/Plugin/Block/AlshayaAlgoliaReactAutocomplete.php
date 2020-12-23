<?php

namespace Drupal\alshaya_algolia_react\Plugin\Block;

use Drupal\alshaya_algolia_react\AlshayaAlgoliaReactBlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\alshaya_algolia_react\Services\AlshayaAlgoliaReactConfigInterface;
use Drupal\alshaya_acm_product_category\ProductCategoryTree;
use Drupal\taxonomy\TermInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\file\FileInterface;
use Drupal\alshaya_custom\Utility;

/**
 * Provides a block to display 'autocomplete block' for mobile.
 *
 * @Block(
 *   id = "alshaya_algolia_react_autocomplete",
 *   admin_label = @Translation("Alshaya Algolia Autocomplete")
 * )
 */
class AlshayaAlgoliaReactAutocomplete extends AlshayaAlgoliaReactBlockBase {

  const PAGE_TYPE = 'search';

  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

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
   * AlshayaAlgoliaReactAutocomplete constructor.
   *
   * @param array $configuration
   *   Configuration array.
   * @param string $plugin_id
   *   Plugin id.
   * @param mixed $plugin_definition
   *   Plugin definition.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\alshaya_algolia_react\Services\AlshayaAlgoliaReactConfigInterface $alshaya_algolia_react_config
   *   Alshaya Algolia React Config.
   * @param \Drupal\alshaya_acm_product_category\ProductCategoryTree $product_category_tree
   *   Product category tree.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    ConfigFactoryInterface $config_factory,
    AlshayaAlgoliaReactConfigInterface $alshaya_algolia_react_config,
    ProductCategoryTree $product_category_tree,
    EntityTypeManagerInterface $entity_type_manager
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $config_factory;
    $this->alshayaAlgoliaReactConfig = $alshaya_algolia_react_config;
    $this->productCategoryTree = $product_category_tree;
    $this->termStorage = $entity_type_manager->getStorage('taxonomy_term');
    $this->fileStorage = $entity_type_manager->getStorage('file');
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
      $container->get('config.factory'),
      $container->get('alshaya_algoila_react.alshaya_algolia_react_config'),
      $container->get('alshaya_acm_product_category.product_category_tree'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Get common configuration for Algolia pages.
    $common_config = $this->alshayaAlgoliaReactConfig->getAlgoliaReactCommonConfig(self::PAGE_TYPE);

    // Get algola settings for lhn menu.
    $config = $this->configFactory->get('alshaya_search_algolia.settings');
    $show_terms_in_lhn = $config->get('show_terms_in_lhn');
    // Menu level is upto L3 when lhn config is all.
    // Default menu level is upto L1.
    $maximum_depth_lhn = ($show_terms_in_lhn == 'all' ? '3' : '1');

    // Add attributes_to_sort_by_name config in drupalSettings.
    $attributes_to_sort_by_name = $this->configFactory
      ->get('alshaya_algolia_react.settings')
      ->get('attributes_to_sort_by_name') ?? [];

    $libraries = [
      'alshaya_algolia_react/autocomplete',
      'alshaya_white_label/algolia_search',
      'alshaya_white_label/slick_css',
    ];
    $display_settings = $this->configFactory->get('alshaya_acm_product.display_settings');
    if ($display_settings->get('color_swatches_show_product_image')) {
      $libraries[] = 'alshaya_white_label/plp-swatch-hover';
    }
    // Get common config and merge with new array.
    $algoliaSearchValues = [
      'local_storage_expire' => 15,
      'maximumDepthLhn' => $maximum_depth_lhn,
      'attributes_to_sort_by_name' => $attributes_to_sort_by_name,
    ];
    $autocomplete = $common_config['autocomplete'];
    $reactTeaserView = $common_config['commonReactTeaserView'];
    $commonAlgoliaSearchValues = $common_config['commonAlgoliaSearch'];
    $algoliaSearch = array_merge($commonAlgoliaSearchValues, $algoliaSearchValues);
    $algoliaSearch[self::PAGE_TYPE] = $common_config[self::PAGE_TYPE];

    // Get sub categories information.
    $term = $this->productCategoryTree->getCategoryTermFromRoute();
    if ($term instanceof TermInterface) {
      $group_sub_category_enabled = $term->get('field_group_by_sub_categories')->getValue();
      if ($group_sub_category_enabled) {
        $sub_categories = $term->get('field_select_sub_categories_plp')->getValue();
        $subcategories = [];
        foreach ($sub_categories as $term_id) {
          $subcategory = $this->termStorage->load($term_id['value']);

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
      }
    }
    $algoliaSearch['subCategories'] = $subcategories;

    return [
      '#type' => 'markup',
      '#markup' => '<div id="alshaya-algolia-autocomplete"></div>',
      '#attached' => [
        'library' => $libraries,
        'drupalSettings' => [
          'algoliaSearch' => $algoliaSearch,
          'autocomplete' => $autocomplete,
          'reactTeaserView' => $reactTeaserView,
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return Cache::mergeContexts(parent::getCacheContexts(), [
      'languages',
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    return Cache::mergeTags(parent::getCacheTags(), [
      'config:alshaya_search.settings',
    ]);
  }

}
