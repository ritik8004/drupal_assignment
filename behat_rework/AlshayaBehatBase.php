<?php

use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Dumper;

class AlshayaBehatBase {

  protected $parser;

  protected $dumper;

  protected $collectedFiles = [];

  protected $markets = ['kw', 'sa', 'ar'];

  public function __construct() {
    $this->parser = new Parser();
    $this->dumper = new Dumper();
    $this->collectedFiles = [];
  }

  public function collectYamlFiles() {
    foreach($this->markets as $market) {
      $market_common = [];
      if (file_exists(TEMPLATE_DIR . "/variables/markets/$market.yml")) {
        $market_common[] = TEMPLATE_DIR . "/variables/markets/$market.yml";
      }
      $files[$market] = array_merge([TEMPLATE_DIR . '/variables/common.yml'], $market_common);

      $directory = TEMPLATE_DIR . '/variables/brands';
      $brands_dirs = new DirectoryIterator($directory);

      foreach ($brands_dirs as $brands_dir) {

        $current_brand = $brands_dir->getBasename();
        $files["{$current_brand}_{$market}"] = $files[$market];

        if (!$brands_dir->isDot() && $brands_dir->isDir()) {

          if (file_exists($brands_dir->getPathname() . "/{$current_brand}.yml")) {
            $files["{$current_brand}_{$market}"] = array_merge(
              $files["{$current_brand}_{$market}"],
              [$brands_dir->getPathname() . "/{$current_brand}.yml"]
            );
          }

          if (file_exists($brands_dir->getPathname() . "/markets/$market.yml")) {
            $files["{$current_brand}_{$market}"] = array_merge(
              $files["{$current_brand}_{$market}"],
              [$brands_dir->getPathname() . "/markets/$market.yml"]
            );
          }


          $env_directory = $brands_dir->getPathname() . "/env";
          $env_dirs = new DirectoryIterator($env_directory);

          foreach ($env_dirs as $env_dir) {
            if (!$env_dir->isDot() && $env_dir->isDir()) {
              $current_env = $env_dir->getBasename();
              $final_key = "{$current_brand}-{$market}-{$current_env}";

              $this->collectedFiles[$final_key] = $files["{$current_brand}_{$market}"];
              if (file_exists($env_dir->getPathname() . "/{$current_env}.yml")) {
                $this->collectedFiles[$final_key] = array_merge(
                  $files["{$current_brand}_{$market}"],
                  [$env_dir->getPathname() . "/{$current_env}.yml"]
                );
              }

              $env_market_directory = $env_dir->getPathname() . "/markets/";
              if (file_exists($env_market_directory . "$market.yml")) {

                $this->collectedFiles[$final_key] = array_merge(
                  $this->collectedFiles[$final_key],
                  [$env_market_directory . "$market.yml"]
                );
              }

            }
          }
        }
      }
    }
    return $this->collectedFiles;
  }

  public function getCollectedYamlFiles() {
    return $this->collectedFiles;
  }

  /**
   * Return a final array of merged keys from given yaml files.
   *
   * @param $yaml_files
   *   List of yaml files.
   *
   * @return array|mixed
   *   Return array.
   */
  public function mergeYamlFiles(array $yaml_files) {
    $final_yaml = ['variables' => [], 'tests' => []];

    if(count($yaml_files) < 2) {
      return $this->getParsedContent($yaml_files[0]);
    }

    foreach ($yaml_files as $yaml_file) {
      $yaml_parsed = $this->getParsedContent($yaml_file);

      if (!empty($yaml_parsed['variables'])) {
        $final_yaml['variables'] = array_replace_recursive($final_yaml['variables'], $yaml_parsed['variables']);
      }

      if (!empty($yaml_parsed['tests'])) {
        $final_yaml['tests'] = array_merge($final_yaml['tests'], $yaml_parsed['tests']);
        if (!empty($final_yaml['tests'])) {
          $final_yaml['tests'] = array_unique($final_yaml['tests']);
        }

      }
    }

    return $final_yaml;
  }

  /**
   * Parse content of given yaml file.
   *
   * @param $yaml_file
   *   Convert given file content to array.
   *
   * @return array|mixed
   *   Return an array.
   */
  public function getParsedContent($yaml_file) {
    $content = file_get_contents($yaml_file);
    return !empty($content) ? $this->parser->parse($content) : [];
  }

  /**
   * Prepare behat.yml config from template.
   *
   * @param $file
   * @param $content
   * @param $profile
   */
  public function parePareBehatYaml($file, $content, $profile = NULL) {
    $yaml = $this->getParsedContent($file);
    // Update feature context variables array.
    $featurecontext = &$yaml['suites']['default']['contexts'][0]['FeatureContext'];
    $featurecontext['parameters'] = array_merge_recursive($featurecontext['parameters'], $content['variables']);
    // Set the MinkExtension base_url to current site's base url.
    $yaml['extensions']['Behat\MinkExtension']['base_url'] = 'https://' . $featurecontext['parameters']['base_url'] . '/';
    // Set the folder for report.
//    if (!empty($profile)) {
//      $yaml['formatters'] = [
//        'html' => [
//          'output_path' => "%paths.base%/features/$profile/reports/html/behat"
//        ]
//      ];
//    }
    return $yaml;
  }

  /**
   * Dump given content to given file.
   *
   * @param $path
   * @param bool $appends
   * @param $content
   * @param null $key
   */
  public function dumpYaml($path, $append = FALSE, $content, $key = NULL) {
    $content = !empty($key) ? [$key => $content] : $content;
    $yaml = $this->dumper->dump($content, 10);

    if (!$append) {
      file_put_contents($path, $yaml);
    }
    else {
      file_put_contents($path, $yaml, FILE_APPEND | LOCK_EX);
    }
  }


}
