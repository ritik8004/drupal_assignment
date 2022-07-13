<?php

// phpcs:ignoreFile
// Ingored as getting warning: Possible useless method overriding detected.

namespace Drupal\alshaya_search_api\Utility;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\search_api\LoggerTrait;
use Drupal\search_api\Utility\PostRequestIndexing;

/**
 * Provides a service for indexing items at the end of the page request.
 */
class AlshayaPostRequestIndexing extends PostRequestIndexing {

  use LoggerTrait;

  /**
   * Constructs a new class instance.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($entity_type_manager);
  }

  /**
   * {@inheritdoc}
   */
  public function destruct() {
    try {
      parent::destruct();
    }
    catch (\Throwable $e) {
      // Just log the exception and continue execution.
      $this->getLogger()->error('Error occurred while indexing post request: @error. Trace: @trace', [
        '@error' => $e->getMessage(),
        '@trace' => $e->getTraceAsString(),
      ]);
    }
  }

}
