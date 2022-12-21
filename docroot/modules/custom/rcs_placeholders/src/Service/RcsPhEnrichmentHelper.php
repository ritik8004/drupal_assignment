<?php

namespace Drupal\rcs_placeholders\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Provides helper services for enrichments.
 */
class RcsPhEnrichmentHelper {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

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
   * Constructs an RcsPhEnrichmentHelper object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
    $this->nodeStorage = $entity_type_manager->getStorage('node');
    $this->termStorage = $entity_type_manager->getStorage('taxonomy_term');
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

}
