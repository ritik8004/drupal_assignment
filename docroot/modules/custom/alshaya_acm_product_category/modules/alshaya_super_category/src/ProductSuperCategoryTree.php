<?php

namespace Drupal\alshaya_super_category;

use Drupal\alshaya_acm_product_category\ProductCategoryTree;
use Drupal\alshaya_acm_product_category\ProductCategoryTreeInterface;
use Drupal\Component\Utility\Html;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Url;
use Drupal\taxonomy\TermInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class ProductSuperCategoryTree.
 */
class ProductSuperCategoryTree extends ProductCategoryTree {

  /**
   * Language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $productCategoryTree;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * ProductCategoryTree constructor.
   *
   * @param \Drupal\alshaya_acm_product_category\ProductCategoryTreeInterface $product_category_tree
   *   Entity type manager.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager.
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
   */
  public function __construct(ProductCategoryTreeInterface $product_category_tree, RequestStack $request_stack, EntityTypeManagerInterface $entity_type_manager, LanguageManagerInterface $language_manager, CacheBackendInterface $cache, RouteMatchInterface $route_match, Connection $connection, ConfigFactoryInterface $config_factory) {
    $this->configFactory = $config_factory;
    $this->productCategoryTree = $product_category_tree;
    $this->requestStack = $request_stack;
    parent::__construct($entity_type_manager, $language_manager, $cache, $route_match, $connection);
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
        if ($brand = $request->query->get('brand')) {
          if (!is_numeric($brand)) {
            $params = Url::fromUserInput("/$brand")->getRouteParameters();
            if (!empty($params['taxonomy_term'])) {
              $brand = $params['taxonomy_term'];
            }
          }

          $term = $this->termStorage->load($brand);
        }
      }
    }

    // If term is of 'acq_product_category' vocabulary.
    if ($term instanceof TermInterface && $term->getVocabularyId() == self::VOCABULARY_ID) {
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
    $query->innerJoin('taxonomy_term_hierarchy', 'tth', 'tth.tid = tfd.tid');
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

    $cache_tags = [
      self::CACHE_TAG,
      self::VOCABULARY_ID,
    ];

    $this->cache->set($cid, $term_data, Cache::PERMANENT, $cache_tags);
    return $term_data;
  }

  /**
   * Get root parent of given term.
   *
   * OR get parent of the term by getting term from current route.
   *
   * @param null|object $term
   *   (optional) The term object or nothing.
   *
   * @return \Drupal\taxonomy\TermInterface|mixed|null
   *   Return the parent term object or NULL.
   */
  public function getCategoryTermRootParent($term = NULL) {
    if (empty($term) || !$term instanceof  TermInterface) {
      $term = $this->getCategoryTermFromRoute();
    }

    if ($term instanceof TermInterface && parent::VOCABULARY_ID == $term->bundle()) {
      // Get the top level parent id if parent exists.
      $parents = $this->getSuperCategoryMapping();
      return isset($parents[$term->id()]) ? $parents[$term->id()] : NULL;
    }
    return NULL;
  }

  /**
   * Get super category term from url.
   *
   * @return array|\Drupal\taxonomy\TermInterface|mixed|null
   *   Return array of term or term object or term id.
   */
  public function getCategoryTermRequired() {
    $term = $this->getCategoryTermRootParent();
    if (empty($term)) {
      $parent_terms = $this->getCategoryTreeCached();
      $tid = alshaya_super_category_get_default_term();
      return isset($parent_terms[$tid]) ? $parent_terms[$tid] : NULL;
    }
    return $term;
  }

  /**
   * Cache super category term mapping.
   */
  protected function getSuperCategoryMapping() {
    $langcode = $this->languageManager->getCurrentLanguage()->getId();
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

}
