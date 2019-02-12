<?php

namespace Drupal\alshaya_performance\Commands;

use Drush\Commands\DrushCommands;

/**
 * class AcqSkuDrushCommands
 */
class AlshayaPerformanceDrushCommands extends DrushCommands {

  /**
   * Clears only frontend caches.
   *
   * This is the lighter version of drush cr. It only clears frontend caches,
   * so it make sense to use it instead of drush cr if only .css, .js, .twig
   * or .theme files were changed.
   *
   * @command cr:frontend
   *
   * @aliases crf, cache-rebuild-frontend
   */
  public function cacheRebuildFrontend() {
    alshaya_performance_flush_frontend_caches();
  }
}