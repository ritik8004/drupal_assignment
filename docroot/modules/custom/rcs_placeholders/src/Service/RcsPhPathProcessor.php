<?php

namespace Drupal\rcs_placeholders\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
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
  public static $entityType;

  /**
   * RCS Entity Path.
   *
   * @var string
   */
  public static $entityPath;

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
   * Constructs a new RcsPhPathProcessor instance.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->nodeStorage = $entity_type_manager->getStorage('node');
    $this->termStorage = $entity_type_manager->getStorage('taxonomy_term');
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
      '/' . $request->getDefaultLocale() . '/',
      '/',
      $request->getPathInfo()
    );

    $config = \Drupal::config('rcs_placeholders.settings');

    // Is it a category page?
    $category_prefix = $config->get('category.path_prefix');

    preg_match('/^\/' . $category_prefix . '([^\/\?]*)/', $rcs_path_to_check, $matches);
    if (isset($matches[1])) {
      self::$entityType = 'category';
      self::$entityPath = $matches[1];

      $static[$rcs_path_to_check] = '/taxonomy/term/' . $config->get('category.placeholder_tid');

      $category = $config->get('category.enrichment') ? $this->getEnrichedEntity('category', $matches[1]) : NULL;
      if (isset($category)) {
        self::$entityData = $category->toArray();
        $static[$rcs_path_to_check] = '/taxonomy/term/' . $category->id();
      }

      return $static[$rcs_path_to_check];
    }

    // Is it a product page?
    $product_prefix = $config->get('product.path_prefix');

    preg_match('/^\/' . $product_prefix . '([^\?]*)/', $rcs_path_to_check, $matches);
    if (isset($matches[1])) {
      self::$entityType = 'product';
      self::$entityPath = $matches[1];

      $static[$rcs_path_to_check] = '/node/' . $config->get('product.placeholder_nid');

      $product = $config->get('product.enrichment') ? $this->getEnrichedEntity('product', $matches[1]) : NULL;
      if (isset($product)) {
        self::$entityData = $product->toArray();
        $static[$rcs_path_to_check] = '/node/' . $product->id();
      }

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

}
