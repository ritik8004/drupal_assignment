<?php

namespace Drupal\alshaya_performance\Commands;

use Drupal\Core\File\FileSystem;
use Drush\Commands\DrushCommands;

/**
 * Class AlshayaPerformanceDrushCommands.
 */
class AlshayaPerformanceDrushCommands extends DrushCommands {

  /**
   * File System service.
   *
   * @var \Drupal\Core\File\FileSystem
   */
  protected $fileSystem;

  /**
   * AlshayaPerformanceDrushCommands constructor.
   *
   * @param \Drupal\Core\File\FileSystem $file_system
   *   File System service.
   */
  public function __construct(FileSystem $file_system) {
    $this->fileSystem = $file_system;
  }

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
  public function cacheRebuildFrontend($options = ['twig' => FALSE]) {
    alshaya_performance_flush_frontend_caches($options);
  }

  /**
   * Delete staged extra files.
   *
   * During staging we move all product files and styles dir inside a directory
   * todelete, we delete this directory in separate cron job to save time
   * during staging process. Check scripts/staging/sub-sh/clean-commerce-data.sh
   * for more details.
   *
   * @command alshaya:delete-staged-extra-files
   *
   * @aliases delete-staged-extra-files
   */
  public function deleteStagedExtraFiles() {
    $dir = $this->fileSystem->realpath('public://todelete');
    if (file_exists($dir)) {
      shell_exec(sprintf('rm -rf %s', escapeshellarg($dir)));
    }
  }

}
