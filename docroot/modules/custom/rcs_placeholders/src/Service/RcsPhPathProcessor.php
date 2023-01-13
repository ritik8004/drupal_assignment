<?php

namespace Drupal\rcs_placeholders\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\PathProcessor\InboundPathProcessorInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides a path processor to detect the commerce entities page types.
 *
 * @property \Drupal\Core\Entity\EntityStorageInterface nodeStorage
 */
class RcsPhPathProcessor implements InboundPathProcessorInterface {

  /**
   * Mapping of entity path and url alias.
   *
   * @var array
   */
  protected static $processedPaths = [];

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
   * RCS path to check.
   *
   * @var string
   */
  public static $rcsPathToCheck;

  /**
   * RCS Full Path.
   *
   * @var string
   */
  public static $entityFullPath;

  /**
   * RCS Full Path.
   *
   * @var string
   */
  public static $pageFullPath;

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
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs a new RcsPhPathProcessor instance.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    LanguageManagerInterface $language_manager,
    ModuleHandlerInterface $module_handler,
    ConfigFactoryInterface $config_factory
  ) {
    $this->nodeStorage = $entity_type_manager->getStorage('node');
    $this->termStorage = $entity_type_manager->getStorage('taxonomy_term');
    $this->languageManager = $language_manager;
    $this->moduleHandler = $module_handler;
    $this->configFactory = $config_factory;
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
    // @todo Move the whole logic to use Custom Event and Event Subscribers.
    // Use static cache to improve performance.
    if (isset(self::$processedPaths[$path])) {
      return self::$processedPaths[$path];
    }
    if (!isset(self::$pageFullPath)) {
      $this->processFullPagePath($request);
    }

    // Remove language code from URL.
    $full_path = $rcs_path_to_check = self::$pageFullPath;

    // Allow other modules to alter the path.
    $this->moduleHandler->alter('rcs_placeholders_processor_path', $rcs_path_to_check);
    self::$rcsPathToCheck = $rcs_path_to_check;

    $config = $this->configFactory->get('rcs_placeholders.settings');

    // Is it a category page?
    $category_prefix = $this->getEntityPrefix('category');

    if (str_starts_with($rcs_path_to_check, '/' . $category_prefix)) {
      $this->processCategoryEntity();
      return self::$processedPaths[$full_path];
    }

    // Is it a product page?
    $product_prefix = $config->get('product.path_prefix');

    if (str_starts_with($rcs_path_to_check, '/' . $product_prefix)) {
      self::$entityType = 'product';
      self::$entityPath = substr_replace($rcs_path_to_check, '', 0, strlen($product_prefix) + 1);
      self::$entityPathPrefix = $product_prefix;
      self::$entityFullPath = $full_path;

      self::$processedPaths[$rcs_path_to_check] = '/node/' . $config->get('product.placeholder_nid');

      $product = $config->get('product.enrichment') ? $this->getEnrichedEntity('product', $rcs_path_to_check) : NULL;
      if (isset($product)) {
        self::$entityData = $product->toArray();
        self::$processedPaths[$rcs_path_to_check] = '/node/' . $product->id();
      }

      return self::$processedPaths[$rcs_path_to_check];
    }

    // Is it a promotion page?
    $promotion_prefix = $config->get('promotion.path_prefix');

    if (str_starts_with($rcs_path_to_check, '/' . $promotion_prefix)) {
      self::$entityType = 'promotion';
      self::$entityPath = substr_replace($rcs_path_to_check, '', 0, strlen($promotion_prefix) + 1);
      self::$entityPathPrefix = $promotion_prefix;
      self::$entityFullPath = $full_path;

      self::$processedPaths[$rcs_path_to_check] = '/node/' . $config->get('promotion.placeholder_nid');

      return self::$processedPaths[$rcs_path_to_check];
    }

    // Set current path as default so we do not process twice for same path.
    if (empty(self::$processedPaths[$path])) {
      self::$processedPaths[$path] = $path;
    }

    return self::$processedPaths[$path];
  }

  /**
   * Process the full page path from request and return it without the langcode.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Request object.
   */
  protected function processFullPagePath(Request $request) {
    // The $path value has been processed in case the requested url is the alias
    // of an existing technical path. For example, $path may be /node/12 if the
    // requested url /buy-my-product is an alias for node 12.  For this reason,
    // we use $request->getPathInfo() to get the real requested url instead of
    // $path.
    self::$pageFullPath = str_replace(
      '/' . $this->languageManager->getCurrentLanguage()->getId() . '/',
      '/',
      $request->getPathInfo()
    );
  }

  /**
   * Process the entity and set the static variables.
   */
  protected function processCategoryEntity() {
    $entity_type = 'category';
    $config = $this->getRcsPhSettings();
    $entity_prefix = $config->get("$entity_type.path_prefix");
    $placehodler_id = $config->get("$entity_type.placeholder_tid");
    $entity_url_prefix = '/taxonomy/term/';

    self::$entityType = $entity_type;
    self::$entityPath = substr_replace(self::$rcsPathToCheck, '', 0, strlen($entity_prefix) + 1);
    self::$entityPathPrefix = $entity_prefix;
    self::$entityFullPath = self::$pageFullPath;
    self::$processedPaths[self::$pageFullPath] = $entity_url_prefix . $placehodler_id;

    $entity = $this->isEntityEnrichmentEnabled($entity_type)
      ? $this->getEnrichedEntity($entity_type, self::$pageFullPath)
      : NULL;
    if (isset($entity)) {
      self::$entityData = $entity->toArray();
      self::$processedPaths[self::$pageFullPath] = $entity_url_prefix . $entity->id();
    }
  }

  /**
   * Gets the RCS placeholders config.
   *
   * @return \Drupal\Core\Config\ImmutableConfig
   *   The RCS placeholders config.
   */
  protected function getRcsPhSettings() {
    $static = NULL;
    if (isset($static)) {
      return $static;
    }
    $static = $this->configFactory->get('rcs_placeholders.settings');
    return $static;
  }

  /**
   * Gets the given entity type prefix from the config.
   *
   * @param string $entity_type
   *   The entity type.
   *
   * @return string
   *   The entity prefix.
   */
  protected function getEntityPrefix($entity_type) {
    $config = $this->getRcsPhSettings();
    return $config->get("$entity_type.path_prefix");
  }

  /**
   * Checks if enrichment is enabled for the given entity type.
   *
   * @param string $entity_type
   *   The entity type.
   *
   * @return bool
   *   True if enrichment is enabled else false.
   */
  protected function isEntityEnrichmentEnabled($entity_type) {
    $config = $this->getRcsPhSettings();
    return $config->get("$entity_type.enrichment");
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
    // Filter out the front and back slash.
    $slug = trim($slug, '/');

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
   * Returns enriched commerce entity based on current path.
   *
   * @param string $type
   *   The commerce entity type (product, category).
   * @param string $path
   *   The overridden path url of the entity.
   *
   * @return object|null
   *   The loaded commerce entity if field matching the current path.
   */
  public function getEnrichedEntityByPath(string $type, string $path) {
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
      $query->condition('field_target_link', $path);
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

  /**
   * Returns the flipped mapping of entity path and path alias.
   *
   * @param string $path
   *   The entity path.
   *
   * @return string
   *   Returns the path alias of the entity path.
   */
  public static function getOrignalPathFromProcessed(string $path): string {
    $processed_paths = array_flip(self::$processedPaths);
    return $processed_paths[$path] ?? $path;
  }

  /**
   * Returns full path with prefix.
   *
   * @param bool $trim
   *   Trim the front slash from start and end.
   *
   * @return string
   *   Full path with prefix if available.
   */
  public static function getFullPath(bool $trim = TRUE) {
    if (empty(self::$entityType)) {
      return '';
    }
    $url = self::$entityFullPath;
    // Trim the front slash.
    if ($trim) {
      $url = trim($url, '/');
    }

    return $url;
  }

  /**
   * Returns url key.
   *
   * @param bool $trim
   *   Trim the front slash from start and end.
   *
   * @return string
   *   The URL key of the current entity path.
   */
  public static function getUrlKey(bool $trim = TRUE) {
    $url = self::$entityPath;
    // Trim the front slash.
    if ($trim) {
      $url = trim($url, '/');
    }

    return $url;
  }

}
