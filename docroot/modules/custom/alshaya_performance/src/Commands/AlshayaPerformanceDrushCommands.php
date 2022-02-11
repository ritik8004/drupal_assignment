<?php

namespace Drupal\alshaya_performance\Commands;

use Drupal\Core\File\FileSystemInterface;
use Drush\Commands\DrushCommands;

/**
 * Class Alshaya Performance Drush Commands.
 */
class AlshayaPerformanceDrushCommands extends DrushCommands {

  /**
   * File System service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * AlshayaPerformanceDrushCommands constructor.
   *
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   File System service.
   */
  public function __construct(FileSystemInterface $file_system) {
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
   * @option twig
   *   Include twig template files in cache invalidation.
   */
  public function cacheRebuildFrontend($options = ['twig' => FALSE]) {
    $clear_twig = $options['twig'];

    if ($clear_twig) {
      $this->output()->writeln("Including twig cache invalidation.");
    }

    alshaya_performance_flush_frontend_caches($clear_twig);
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
