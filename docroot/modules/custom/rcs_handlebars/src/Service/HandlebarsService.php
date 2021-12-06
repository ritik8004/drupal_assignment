<?php

namespace Drupal\rcs_handlebars\Service;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Component\Utility\NestedArray;

/**
 * Service for Handlebars templates.
 */
class HandlebarsService {

  /**
   * The file system.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * Th module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The list of translatable strings.
   *
   * @var array
   */
  protected $strings = [];

  /**
   * Constructor for Handlebars Service.
   *
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   File system service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   */
  public function __construct(FileSystemInterface $file_system,
                              ModuleHandlerInterface $module_handler) {
    $this->fileSystem = $file_system;
    $this->moduleHandler = $module_handler;
  }

  /**
   * Gets the relative path of a Uri.
   *
   * @param string $uri
   *   The file Uri.
   *
   * @return string
   *   The relative path.
   */
  protected function getRelativePath($uri) {
    return '/' . str_replace($_SERVER['DOCUMENT_ROOT'] . '/',
      '',
      $this->fileSystem->realpath($uri)
    );
  }

  /**
   * Returns the directory to store Handlebars js.
   *
   * @return string
   *   The public directory.
   */
  protected function getDir() {
    return 'public://rcs_handlebars';
  }

  /**
   * Create folders for the parts of the path.
   *
   * @param string $path
   *   The initial path.
   *
   * @return string
   *   The string containing path.
   */
  protected function prepareDirectories($path) {
    $dir = $this->getDir();
    if (!file_exists($dir)) {
      $this->fileSystem->prepareDirectory($dir, FileSystemInterface::CREATE_DIRECTORY);
    }

    // Build list of folders.
    $dirs = explode('.', $path);

    // We don't need folders for the entire tree.
    $dirs = array_slice($dirs, 0, 2);

    foreach ($dirs as $subdir) {
      $dir = "$dir/$subdir";
      if (!file_exists($dir)) {
        $this->fileSystem->prepareDirectory($dir, FileSystemInterface::CREATE_DIRECTORY);
      }
    }

    return $dir;
  }

  /**
   * Checks if the string ends with searched string.
   *
   * @param string $haystack
   *   The string to search in.
   * @param string $needle
   *   The string to look for.
   *
   * @return bool
   *   TRUE/FALSE.
   */
  protected function endsWith($haystack, $needle) {
    return substr($haystack, -strlen($needle)) === $needle;
  }

  /**
   * Invoke hook to collect list of libraries to attach to entities.
   *
   * @param string $hook
   *   The hook name.
   * @param array $args
   *   The arguments.
   *
   * @return array
   *   The result array.
   */
  protected function invoke($hook, array $args = []) {
    $return = [];
    $implementations = $this->moduleHandler->getImplementations($hook);
    foreach ($implementations as $module) {
      $function = $module . '_' . $hook;
      $result = call_user_func_array($function, $args);
      if (isset($result) && is_array($result)) {
        // Invert keys.
        $result = array_flip($result);
        // Add extension name.
        foreach ($result as $key => $item) {
          $result[$key] = $module;
        }
        $return = NestedArray::mergeDeep($return, $result);
      }
      elseif (isset($result)) {
        $return[] = $result;
      }
    }

    return $return;
  }

  /**
   * Attaches respective libraries to entities.
   *
   * @param array $build
   *   The build array.
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   */
  public function attachLibraries(array &$build, EntityInterface $entity) {
    // Invoke hook to get default libraries.
    $rcs_handlebars_libraries = $this->invoke('rcs_handlebars_templates', [$entity]);

    // Allow other modules to alter libraries.
    $this->moduleHandler->alter('rcs_handlebars_templates', $rcs_handlebars_libraries, $entity);

    // Attach all libraries.
    $build['#attached']['library'][] = 'rcs_handlebars/main';
    foreach ($rcs_handlebars_libraries as $library => $module) {
      $build['#attached']['library'][] = "$module/$library";
    }
  }

  /**
   * Scans templates to find translatable strings.
   *
   * @param string $contents
   *   The contents of Handlebars templates.
   *
   * @return string
   *   The translatable strings.
   */
  protected function scanTranslatableStrings(string $contents) {
    $regex = "~
      {{\s*                                  # match opening brackets
      t\s\s*                                 # match t() helper
      [\"|'](.*?)[\"|']\s*                   # capture string
      ~sx";

    preg_match_all($regex, $contents, $matches);

    $strings = [];
    foreach ($matches[1] as $string) {
      $strings[] = "Drupal.t('$string');";
    }

    return implode('', array_unique($strings));
  }

  /**
   * Dynamically generates the js script.
   *
   * @param string $extension
   *   The module name.
   * @param string $id
   *   The library id.
   * @param string $path
   *   The path to the template.
   *
   * @return string
   *   The generated script.
   */
  protected function generateScript($extension, $id, $path) {
    // Load template.
    $module_path = drupal_get_path('module', $extension);
    $contents = file_get_contents("$module_path/$path");
    $json = json_encode($contents, JSON_UNESCAPED_UNICODE);

    // @todo remove Handlebars comments from templates.
    // Add comments in the dynamic js to allow Drupal Locale to find
    // the translatable strings.
    $strings = $this->scanTranslatableStrings($contents);

    // Prepare script.
    $script = "window.rcsHandlebarsTemplates = window.rcsHandlebarsTemplates || {};\n";
    $script .= "window.rcsHandlebarsTemplates['$id'] = $json\n// $strings\n";

    return $script;
  }

  /**
   * Renders Handlebars templates as javascript libraries.
   *
   * @param array $libraries
   *   The list of libraries.
   * @param string $extension
   *   The module name.
   */
  public function libraryInfoAlter(array &$libraries, $extension) {
    foreach ($libraries as $id => $library) {
      if (empty($library['js'])) {
        continue;
      }

      foreach ($library['js'] as $path => $details) {
        // Check if this is a Handlebars template.
        if (!$this->endsWith($path, '.handlebars')) {
          continue;
        }

        // Make sure that folder structure is created.
        $dir = $this->prepareDirectories("$extension/$id");
        $uri = $dir . '/' . basename($path) . '.js';

        // Check if file exists and continue.
        if (!file_exists($uri)) {
          $script = $this->generateScript($extension, $id, $path);
          $this->fileSystem->saveData($script, $uri, 1);
        }

        // Replace the library path.
        $libraries[$id]['js'][$this->getRelativePath($uri)] = $details;
        unset($libraries[$id]['js'][$path]);
      }
    }
  }

  /**
   * Removes dynamically generated js files.
   */
  public function cacheClear() {
    $this->fileSystem->deleteRecursive($this->getDir());
  }

}
