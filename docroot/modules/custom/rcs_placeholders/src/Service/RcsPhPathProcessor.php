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
    $config = \Drupal::config('rcs_placeholders.settings');

    // Is it a category page?
    $category_prefix = $config->get('category.path_prefix');

    preg_match('/^\/' . $category_prefix . '([^\/\?]*)/', $path, $matches);
    if (isset($matches[1])) {
      $request->server->set('rcs_entity_type', 'category');
      $request->server->set('rcs_entity_path', $matches[1]);

      $category = $config->get('category.enrichment') ? $this->getEnrichedEntity('category', $matches[1]) : NULL;
      if (isset($category)) {
        $request->server->set('rcs_entity_data', $category->toArray());
        return '/taxonomy/term/' . $category->id();
      }
      else {
        return '/taxonomy/term/' . $config->get('category.placeholder_tid');
      }
    }

    // Is it a product page?
    $product_prefix = $config->get('product.path_prefix');

    preg_match('/^\/' . $product_prefix . '([^\?]*)/', $path, $matches);
    if (isset($matches[1])) {
      $request->server->set('rcs_entity_type', 'product');
      $request->server->set('rcs_entity_path', $matches[1]);

      $product = $config->get('product.enrichment') ? $this->getEnrichedEntity('product', $matches[1]) : NULL;
      if (isset($product)) {
        $request->server->set('rcs_entity_data', $product->toArray());
        return '/node/' . $product->id();
      }
      else {
        return '/node/' . $config->get('product.placeholder_nid');
      }
    }

    return $path;
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
  public function getEnrichedEntity($type, $slug) {
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
