<?php

namespace Drupal\rcs_placeholders\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\PathProcessor\InboundPathProcessorInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides a path processor to detect the commerce entities page types.
 *
 * @property \Drupal\Core\Entity\EntityStorageInterface nodeStorage
 */
class RcsPhPathProcessor implements InboundPathProcessorInterface {

  /**
   * RCS Entity Type.
   *
   * @var string
   */
  public static $entityType = NULL;

  /**
   * RCS Entity Path.
   *
   * @var string
   */
  public static $entityPath;

  /**
   * RCS Entity Path Prefix.
   *
   * It is stored from config here.
   * Allow using this directly from the variable in other places.
   *
   * @var string
   */
  public static $entityPathPrefix;

  /**
   * RCS Entity data.
   *
   * @var array|null
   */
  public static $entityData;

  /**
   * The node storage.
   *
   * @var \Drupal\node\NodeStorageInterface
   */
  protected $nodeStorage;

  /**
   * The taxonomy storage.
   *
   * @var \Drupal\taxonomy\TermStorageInterface
   */
  protected $termStorage;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Constructs a new RcsPhPathProcessor instance.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager service.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    LanguageManagerInterface $language_manager
  ) {
    $this->nodeStorage = $entity_type_manager->getStorage('node');
    $this->termStorage = $entity_type_manager->getStorage('taxonomy_term');
    $this->languageManager = $language_manager;
  }

  /**
   * Alters the path for commerce entities.
   *
   * Look for commerce entities prefix in URL and render the associated
   * placeholder entity.
   *
   * @param string $path
   *   The path to process, with a leading slash.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The HttpRequest object representing the request to process. Note, if this
   *   method is being called via the path_processor_manager service and is not
   *   part of routing, the current request object must be cloned before being
   *   passed in.
   *
   * @return string
   *   The processed path.
   */
  public function processInbound($path, Request $request) {
    static $static = [];

    // Use static cache to improve performance.
    if (isset($static[$path])) {
      return $static[$path];
    }

    // The $path value has been processed in case the requested url is the alias
    // of an existing technical path. For example, $path may be /node/12 if the
    // requested url /buy-my-product is an alias for node 12.  For this reason,
    // we use $request->getPathInfo() to get the real requested url instead of
    // $path.
    // Remove language code from URL.
    $rcs_path_to_check = str_replace(
      '/' . $this->languageManager->getCurrentLanguage()->getId() . '/',
      '/',
      $request->getPathInfo()
    );

    $config = \Drupal::config('rcs_placeholders.settings');

    // Is it a category page?
    $category_prefix = $config->get('category.path_prefix');

    if (strpos($rcs_path_to_check, '/' . $category_prefix) === 0) {
      // Is there any department page for the cateogry page?
      if (function_exists('alshaya_rcs_main_menu_get_department_pages')) {
        $department_pages = alshaya_rcs_main_menu_get_department_pages();
        if (array_key_exists($path, $department_pages)) {
          return $path;
        }
      }

      self::$entityType = 'category';
      self::$entityPath = substr_replace($rcs_path_to_check, '', 0, strlen($category_prefix) + 1);
      self::$entityPathPrefix = $category_prefix;

      $static[$rcs_path_to_check] = '/taxonomy/term/' . $config->get('category.placeholder_tid');

      $category = $config->get('category.enrichment') ? $this->getEnrichedEntity('category', self::$entityPath) : NULL;
      if (isset($category)) {
        self::$entityData = $category->toArray();
        $static[$rcs_path_to_check] = '/taxonomy/term/' . $category->id();
      }

      return $static[$rcs_path_to_check];
    }

    // Is it a product page?
    $product_prefix = $config->get('product.path_prefix');

    if (strpos($rcs_path_to_check, '/' . $product_prefix) === 0) {
      self::$entityType = 'product';
      self::$entityPath = substr_replace($rcs_path_to_check, '', 0, strlen($product_prefix) + 1);
      self::$entityPathPrefix = $product_prefix;

      $static[$rcs_path_to_check] = '/node/' . $config->get('product.placeholder_nid');

      $product = $config->get('product.enrichment') ? $this->getEnrichedEntity('product', self::$entityPath) : NULL;
      if (isset($product)) {
        self::$entityData = $product->toArray();
        $static[$rcs_path_to_check] = '/node/' . $product->id();
      }

      return $static[$rcs_path_to_check];
    }

    // Is it a promotion page?
    $promotion_prefix = $config->get('promotion.path_prefix');

    if (strpos($rcs_path_to_check, '/' . $promotion_prefix) === 0) {
      self::$entityType = 'promotion';
      self::$entityPath = substr_replace($rcs_path_to_check, '', 0, strlen($promotion_prefix) + 1);
      self::$entityPathPrefix = $promotion_prefix;

      $static[$rcs_path_to_check] = '/node/' . $config->get('promotion.placeholder_nid');

      return $static[$rcs_path_to_check];
    }

    // Set current path as default so we do not process twice for same path.
    if (empty($static[$path])) {
      $static[$path] = $path;
    }

    return $static[$path];
  }

  /**
   * Returns enriched commerce entity based on the slug.
   *
   * @param string $type
   *   The commerce entity type (product, category).
   * @param string $slug
   *   The slug (unique identifier) of the commerce entity.
   *
   * @return object|null
   *   The loaded commerce entity if any matching the slug.
   */
  public function getEnrichedEntity(string $type, string $slug) {
    $entity = NULL;
    $storage = NULL;

    if ($type == 'product') {
      $storage = $this->nodeStorage;
    }
    elseif ($type == 'category') {
      $storage = $this->termStorage;
    }

    if (!is_null($storage)) {
      $query = $storage->getQuery();
      $query->condition('field_' . $type . '_slug', $slug);
      $result = $query->execute();
      if (!empty($result)) {
        $entity = $storage->load(array_shift($result));
      }
    }
    return $entity;
  }

  /**
   * Returns TRUE if we are on RCS page.
   *
   * @return bool
   *   Returns TRUE if its Rcs page.
   */
  public static function isRcsPage() {
    return self::$entityType != NULL;
  }

}
