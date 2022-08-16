<?php

namespace Drupal\alshaya_super_category;

use Drupal\alshaya_acm_product_category\ProductCategoryTree;
use Drupal\alshaya_acm_product_category\ProductCategoryTreeInterface;
use Drupal\Component\Utility\Html;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\path_alias\AliasManagerInterface;
use Drupal\Core\Path\CurrentPathStack;
use Drupal\Core\Url;
use Drupal\taxonomy\TermInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\alshaya_acm_product\ProductCategoryHelper;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Theme\ThemeManagerInterface;

/**
 * Class Product Super Category Tree.
 */
class ProductSuperCategoryTree extends ProductCategoryTree {

  /**
   * Language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $productCategoryTree;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The path alias manager.
   *
   * @var \Drupal\path_alias\AliasManagerInterface
   */
  protected $aliasManager;

  /**
   * Product Category Helper service object.
   *
   * @var \Drupal\alshaya_acm_product\ProductCategoryHelper
   */
  protected $productCategoryHelper;

  /**
   * Module Handler service object.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * ProductCategoryTree constructor.
   *
   * @param \Drupal\alshaya_acm_product_category\ProductCategoryTreeInterface $product_category_tree
   *   Entity type manager.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   * @param \Drupal\Core\Path\CurrentPathStack $current_path
   *   The current path.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   Entity Repository.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   Language manager.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   Cache Backend service for alshaya.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   Route match service.
   * @param \Drupal\Core\Database\Connection $connection
   *   Database connection.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\path_alias\AliasManagerInterface $alias_manager
   *   The path alias manager.
   * @param \Drupal\alshaya_acm_product\ProductCategoryHelper $product_category_helper
   *   Product Category Helper service object.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Module Handler service object.
   * @param \Drupal\Core\Theme\ThemeManagerInterface $theme_manager
   *   The Theme Manager service.
   */
  public function __construct(ProductCategoryTreeInterface $product_category_tree,
                              RequestStack $request_stack,
                              CurrentPathStack $current_path,
                              EntityTypeManagerInterface $entity_type_manager,
                              EntityRepositoryInterface $entity_repository,
                              LanguageManagerInterface $language_manager,
                              CacheBackendInterface $cache,
                              RouteMatchInterface $route_match,
                              Connection $connection,
                              ConfigFactoryInterface $config_factory,
                              AliasManagerInterface $alias_manager,
                              ProductCategoryHelper $product_category_helper,
                              ModuleHandlerInterface $module_handler,
                              ThemeManagerInterface $theme_manager) {
    $this->configFactory = $config_factory;
    $this->productCategoryTree = $product_category_tree;
    $this->aliasManager = $alias_manager;
    $this->themeManager = $theme_manager;
    parent::__construct($entity_type_manager, $entity_repository, $language_manager, $cache, $route_match, $request_stack, $current_path, $connection, $product_category_helper, $module_handler);
  }

  /**
   * Get the term object from current route.
   *
   * @return \Drupal\Core\Entity\EntityInterface|mixed|null
   *   Return the taxonomy term object if found else NULL.
   */
  public function getCategoryTermFromRoute() {
    $term = parent::getCategoryTermFromRoute();

    if (empty($term)) {
      $request = $this->requestStack->getCurrentRequest();
      $current_uri = $request->getRequestUri();
      $path_parts = pathinfo($current_uri);
      $term_path = explode('/', $path_parts['dirname']);
      if (!empty($term_path[2])) {
        $term = $this->getTermByName($term_path[2]);
        if (!empty($term->tid)) {
          $term = $this->termStorage->load($term->tid);
        }
      }
      elseif ($request->get('_route') == 'view.search.page') {
        $brand = $request->query->get('brand');

        if (is_numeric($brand)) {
          $brand = '';
          $request->query->set('brand', '');
        }
        else {
          try {
            $params = Url::fromUserInput("/$brand")->getRouteParameters();
            if (!empty($params['taxonomy_term'])) {
              $brand = $params['taxonomy_term'];
            }
          }
          catch (\Exception) {
            // Ignore the value, someone is simply trying to mess up with system
            // using random value in GET.
            $brand = '';
            $request->query->set('brand', '');
          }
        }

        if (!empty($brand)) {
          $term = $this->termStorage->load($brand);
        }
      }
    }

    // If term is of 'acq_product_category' vocabulary.
    if ($term instanceof TermInterface && $term->bundle() == self::VOCABULARY_ID) {
      return $term;
    }

    return $term;
  }

  /**
   * Get term object by name.
   *
   * @param string $name
   *   The term label.
   * @param string $langcode
   *   The language code.
   *
   * @return array
   *   Return the result array.
   */
  protected function getTermByName($name, $langcode = NULL) {
    if (empty($langcode)) {
      $langcode = $this->languageManager->getCurrentLanguage()->getId();
    }
    $query = $this->connection->select('taxonomy_term_field_data', 'tfd');
    $query->fields('tfd', ['tid', 'name', 'description__value']);
    $query->innerJoin('taxonomy_term__parent', 'tth', 'tth.entity_id = tfd.tid');
    $query->innerJoin('taxonomy_term__field_category_include_menu', 'ttim', 'ttim.entity_id = tfd.tid AND ttim.langcode = tfd.langcode');
    $query->condition('ttim.field_category_include_menu_value', 1);
    $query->condition('tfd.langcode', $langcode);
    $query->condition('tfd.vid', self::VOCABULARY_ID);
    $query->where("REPLACE(LOWER(tfd.name), ' ','-') LIKE :name", [':name' => Html::cleanCssIdentifier($name)]);
    $query->orderBy('tfd.weight', 'ASC');
    return $query->execute()->fetch();
  }

  /**
   * Get top level category items.
   *
   * @param string $langcode
   *   (optional) The language code.
   *
   * @return array
   *   Processed term data.
   */
  public function getCategoryRootTerms($langcode = NULL) {
    if (empty($langcode)) {
      $langcode = $this->languageManager->getCurrentLanguage()->getId();
    }

    $cid = self::CACHE_ID . '_' . $langcode;

    if ($term_data = $this->cache->get($cid)) {
      return $term_data->data;
    }

    // Get all child terms for the given parent.
    $term_data = $this->getCategoryTree($langcode, 0, FALSE, FALSE);

    $this->cache->set($cid, $term_data, Cache::PERMANENT, [self::CACHE_TAG]);
    return $term_data;
  }

  /**
   * Get root parent of given term.
   *
   * OR get parent of the term by getting term from current route.
   *
   * @param null|object $term
   *   (optional) The term object or nothing.
   * @param string $langcode
   *   (optional) The language code.
   *
   * @return \Drupal\taxonomy\TermInterface|mixed|null
   *   Return the parent term object or NULL.
   */
  public function getCategoryTermRootParent($term = NULL, $langcode = NULL) {
    if (empty($term) || !$term instanceof TermInterface) {
      $term = $this->getCategoryTermFromRoute();
    }

    if ($term instanceof TermInterface && parent::VOCABULARY_ID == $term->bundle()) {
      // Get the top level parent id if parent exists.
      $parents = $this->getSuperCategoryMapping($langcode);
      return $parents[$term->id()] ?? NULL;
    }
    return NULL;
  }

  /**
   * Get super category term from url.
   *
   * @param null|object $term
   *   (optional) The term object.
   * @param string $langcode
   *   (optional) The language code.
   *
   * @return array|\Drupal\taxonomy\TermInterface|mixed|null
   *   Return array of term or term object or term id.
   */
  public function getCategoryTermRequired($term = NULL, $langcode = NULL) {
    $term = $this->getCategoryTermRootParent($term, $langcode);

    if (empty($term)) {
      $parent_terms = $this->getCategoryTreeCached(0, $langcode);
      $tid = alshaya_super_category_get_default_term($langcode);
      return $parent_terms[$tid] ?? NULL;
    }
    return $term;
  }

  /**
   * Cache super category term mapping.
   *
   * @param string $langcode
   *   (optional) The language code.
   *
   * @return array
   *   Return the associative array of term.
   */
  protected function getSuperCategoryMapping($langcode = NULL) {
    if (empty($langcode)) {
      $langcode = $this->languageManager->getCurrentLanguage()->getId();
    }

    $cid = 'super_category_map_' . $langcode;

    if ($cache_terms = $this->cache->get($cid)) {
      return $cache_terms->data;
    }

    $terms = $this->getCategoryRootTerms();
    $cache_terms = [];
    $cache_terms += $terms;
    // Loop through each parent to map parent key to child.
    foreach (array_keys($terms) as $tid) {
      $childterms = $this->termStorage->loadTree('acq_product_category', $tid, NULL, TRUE);
      foreach ($childterms as $childterm) {
        $cache_terms[$childterm->id()] = $terms[$tid];
      }
    }

    $this->cache->set($cid, $cache_terms, Cache::PERMANENT, [
      ProductCategoryTree::CACHE_TAG,
      ProductCategoryTree::VOCABULARY_ID,
    ]);
    return $cache_terms;
  }

  /**
   * {@inheritdoc}
   */
  public function getL1DepthLevel() {
    $depthLevel = parent::getL1DepthLevel();

    if ($this->configFactory->get('alshaya_super_category.settings')->get('status')) {
      $depthLevel++;
    }

    return $depthLevel;
  }

  /**
   * {@inheritdoc}
   */
  public function getAllParents($term) {
    $parents = parent::getAllParents($term);

    // Remove the first parent if super category is enabled as that is
    // l0 and we need from l1.
    if ($this->configFactory->get('alshaya_super_category.settings')->get('status')) {
      array_shift($parents);
    }

    return $parents;
  }

  /**
   * {@inheritdoc}
   */
  public function getChildTermIds(int $parent = 0) {
    if ($this->configFactory->get('alshaya_super_category.settings')->get('status')) {
      $l1Terms = $this->termStorage->loadTree('acq_product_category', $parent, 2);

      foreach ($l1Terms as $key => $term) {
        // We get 1st and 2nd levels and also check parents
        // (only 2nd level has parents).
        if (empty($this->termStorage->loadParents($term->tid))) {
          unset($l1Terms[$key]);
        }
      }
    }
    else {
      $l1Terms = parent::getChildTermIds($parent);
    }

    return $l1Terms ? array_column($l1Terms, 'name', 'tid') : [];
  }

  /**
   * Gets the brand logos.
   *
   * @param int $tid
   *   Taxonomy term id.
   *
   * @return object
   *   Object containing fields data.
   */
  public function getBrandIcons($tid) {
    // Check for super category status.
    if (!$this->configFactory->get('alshaya_super_category.settings')->get('status')) {
      return [];
    }
    // Supercategory image fields.
    $fields = [
      'active_image' => 'field_logo_active_image',
      'inactive_image' => 'field_logo_inactive_image',
      'header_image' => 'field_logo_header_image',
    ];
    $brand_logos = $brand_logo_data = [];
    $term_data = $this->getCategoryRootTerms();
    $current_language = $this->languageManager->getCurrentLanguage()->getId();
    // Get all the terms data in English for preparing label.
    $term_data_en = ($current_language !== 'en') ? $this->getCategoryRootTerms('en') : $term_data;
    $term_info_en = $term_data_en[$tid] ?? "";
    if (!empty($term_info_en)) {
      $theme = $this->themeManager->getActiveTheme();
      $base_uri = $this->requestStack->getCurrentRequest()->getSchemeAndHttpHost();
      $term_clean_name = Html::cleanCssIdentifier(mb_strtolower($term_info_en['label']));
      $brand_logos['active_image'] = $base_uri . '/' . $theme->getPath() . '/imgs/logos/super-category/' . $term_clean_name . '-active.svg';
      $brand_logos['inactive_image'] = $base_uri . '/' . $theme->getPath() . '/imgs/logos/super-category/' . $term_clean_name . '.svg';
    }
    // Get supercategory logo data.
    foreach ($fields as $field_key => $field) {
      $brand_logo_data[$field_key] = $this->getImageField($tid, $field);
    }
    // Prepare image url for supercategory logo.
    foreach ($brand_logo_data as $key => $logo_data) {
      $id = "field_logo_{$key}_target_id";
      if (!empty($logo_data)) {
        $image = $this->fileStorage->load($logo_data->$id);
        $brand_logos[$key] = file_create_url($image->getFileUri());
      }
    }

    return $brand_logos;
  }

}
