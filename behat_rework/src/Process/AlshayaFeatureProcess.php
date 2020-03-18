<?php

namespace Alshaya\BehatBuild;

use Behat\Gherkin\Node\TableNode;
use FilesystemIterator;
use DirectoryIterator;

/**
 * Class AlshayaFeatureProcess.
 *
 * @package Alshaya\BehatBuild
 */
class AlshayaFeatureProcess {

  protected $basePath;

  protected $site;

  protected $config;

  protected $suiteLocators;

  protected $sourcePath;

  protected $destinationPath;

  protected $validFeatures;

  /**
   * AlshayaFeatureProcess constructor.
   *
   * @param array $parameters
   *   The array of parameters.
   */
  public function __construct(array $parameters) {
    $this->site = $parameters['site'];
    $this->sourcePath = $parameters['template_path'];
    $this->destinationPath = $parameters['build_path'];
    $this->config = $parameters['variables'];
    $this->validFeatures = $parameters['features'] ?? [];
    $this->viewport = $parameters['viewport'];
    $environment = explode('-', $parameters['site']);
    if ($environment[2] == 'dev2') {
      $this->suiteLocators = [
        $this->sourcePath . DIRECTORY_SEPARATOR . 'common' . DIRECTORY_SEPARATOR . 'spc',
        $this->sourcePath . DIRECTORY_SEPARATOR];
    }
    else {
      $this->suiteLocators = [$this->sourcePath . DIRECTORY_SEPARATOR . 'common', $this->sourcePath . DIRECTORY_SEPARATOR];
    }
  }

  /**
   * Generate feature files.
   */
  public function generateFeatureFiles() {
    $featureLocators = $this->preparePathsToCheckForFeatures();

    // Collect all feature files in array.
    $featurePaths = [];
    foreach ($featureLocators as $featureLocator) {
      $featurePaths = array_merge(
        $featurePaths,
        $this->findFeatureFilesInGivenPath($featureLocator)
      );
    }

    // Delete folder and it's files so, we can recreate.
    if (!empty($featurePaths)) {
      $this->deleteFolderAndFiles($this->destinationPath . DIRECTORY_SEPARATOR . $this->site);
    }

    foreach ($featurePaths as $file_name => $featurePath) {
      $file_content = file_get_contents($featurePath);
      if (empty($file_content)) {
        continue;
      }
      // Replace variables with value.
      $file_content = $this->replaceVariables($file_content);
      // Create folders and files if not exists.
      $new_file_path = $this->createFolderAndFiles($file_name);
      try {
        file_put_contents($new_file_path, $file_content);
      }
      catch (\Exception $e) {
        throw new \Exception('could not generate file:' . $new_file_path . '::' . $e->getMessage());
      }
    }
  }

  /**
   * Prepare locations array to check for features.
   *
   * @return array
   *   Return array of paths.
   */
  protected function preparePathsToCheckForFeatures():array {
    $suiteLocators = $this->suiteLocators;
    $brand_feature_dir = explode('-', $this->site);

    $newLocators = array_map(
      function ($dir_key) use ($brand_feature_dir) {
        $path = $this->sourcePath . DIRECTORY_SEPARATOR . implode('/', array_slice($brand_feature_dir, 0, array_search($dir_key, $brand_feature_dir) + 1));
        return $path;
      }, $brand_feature_dir);

    $this->suiteLocators = array_merge($suiteLocators, $newLocators);
    return $this->suiteLocators;
  }

  /**
   * Delete existing folders and files from given destination dir.
   *
   * @param string $rootPath
   *   The destination directory path.
   */
  protected function deleteFolderAndFiles($rootPath) {
    if (!is_dir($rootPath)) {
      return;
    }

    foreach (new DirectoryIterator($rootPath) as $fileToDelete) {
      if ($fileToDelete->isDot()) {
        continue;
      }

      if ($fileToDelete->isFile()) {
        unlink($fileToDelete->getPathName());
      }

      if ($fileToDelete->isDir()) {
        $this->deleteFolderAndFiles($fileToDelete->getPathName());
      }
    }

    rmdir($rootPath);
  }

  /**
   * Create missing folder and file based on given file name.
   *
   * @param string $file_name
   *   The file name.
   *
   * @return string
   *   return a complete destination path for given file name.
   */
  protected function createFolderAndFiles($file_name) {
    if (!is_dir($this->destinationPath)) {
      mkdir($this->destinationPath);
    }

    if (!is_dir($this->destinationPath . DIRECTORY_SEPARATOR . $this->site)) {
      mkdir($this->destinationPath . DIRECTORY_SEPARATOR . $this->site);
    }
    return $this->destinationPath . DIRECTORY_SEPARATOR . $this->site . DIRECTORY_SEPARATOR . $file_name;
  }

  /**
   * Replace variables in given text.
   *
   * @param string $file_content
   *   The file content.
   *
   * @return string
   *   Return string with variable replaced with actual value.
   */
  protected function replaceVariables($file_content) {
    if (empty($file_content)) {
      return '';
    }

    // Check for variables that is inside curly brackets.
    preg_match_all('/{([^}]*)}/', $file_content, $matches);
    $variables = $this->config;
    // Generate array of variable values in the same order as the variables
    // found from the content.
    $replacement_variables = array_map(function ($var) use ($variables) {
      if (!isset($variables[$var])) {
        return '';
      }

      if (!is_array($variables[$var])) {
        return $variables[$var];
      }
      elseif ($this->isMultiDimensional($variables[$var])) {
        $table = new TableNode($variables[$var]);
        return $table->getTableAsString();
      }
      elseif ($this->isTagVariable($var) && !empty($variables[$var])) {
        return implode(' ', $variables[$var]);
      }
    }, $matches[1]);
    // Create an array of variable, with variable wrapped in curly brackets
    // as key and their actual value (from gathered config).
    $replacements = array_combine($matches[0], $replacement_variables);

    return strtr($file_content, $replacements);
  }

  /**
   * Check if the array is multidimensional array.
   *
   * @param array $array
   *   The array that needs to be validated.
   *
   * @return bool
   *   Return true if multidimensional else false.
   */
  protected function isMultiDimensional(array $array) {
    return !empty(array_filter($array, function ($e) {
      return is_array($e);
    }));
  }

  /**
   * Check if the given variable is tag variable.
   *
   * @param string $var
   *   The string value.
   *
   * @return bool
   *   Return true if given value is tag variable.
   */
  protected function isTagVariable($var) {
    return strpos($var, '@') !== FALSE;
  }

  /**
   * Loads feature files paths from provided path.
   *
   * @param string $path
   *   The directory path to find feature files.
   *
   * @return string[]
   *   Return array of feature file paths.
   */
  private function findFeatureFilesInGivenPath($path):array {
    $absolutePath = $this->findAbsolutePath($path);

    if (!$absolutePath) {
      return [];
    }

    if (is_file($absolutePath)) {
      return array($absolutePath);
    }

    $collect_files = [];
    $files = new FilesystemIterator($absolutePath, FilesystemIterator::KEY_AS_FILENAME);
    foreach ($files as $key => $file) {
      if (!$file->isFile()) {
        continue;
      }

      if (!empty($this->validFeatures)
          && !in_array(str_replace('.feature', '', $key), $this->validFeatures)
      ) {
        continue;
      }

      $collect_files[$key] = $file->getPathname();
    }

    $paths = array_map('strval', $collect_files);
    uasort($paths, 'strnatcasecmp');
    return $paths;
  }

  /**
   * Finds absolute path for provided relative (relative to base features path).
   *
   * @param string $path
   *   Relative path.
   *
   * @return bool|string
   *   return realpath of given path else false.
   */
  private function findAbsolutePath($path) {
    if (is_file($path) || is_dir($path)) {
      return realpath($path);
    }

    if ($this->basePath === NULL) {
      return FALSE;
    }

    if (is_file($this->basePath . DIRECTORY_SEPARATOR . $path)
        || is_dir($this->basePath . DIRECTORY_SEPARATOR . $path)
    ) {
      return realpath($this->basePath . DIRECTORY_SEPARATOR . $path);
    }

    return FALSE;
  }

}
