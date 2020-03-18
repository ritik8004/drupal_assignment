<?php

namespace Alshaya\BehatBuild;

use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Dumper;
use Symfony\Component\Config\Definition\Processor;

/**
 * Class AlshayaYamlProcess.
 *
 * @package Alshaya\BehatBuild
 */
class AlshayaYamlProcess {

  const PROFILE_COLLECTION = 'profiles/collection.yml';

  protected $parser;

  protected $dumper;

  protected $processor;

  protected $configValidator;

  protected $collectedFilesList;

  protected $alshayaMarkets = ['kw', 'sa', 'ae'];

  protected $alshayaLanguages = ['en', 'ar'];

  protected $buildDir;

  protected $collectionFile;

  /**
   * AlshayaYamlProcess constructor.
   */
  public function __construct($dir) {
    $this->parser = new Parser();
    $this->dumper = new Dumper();
    $this->processor = new Processor();
    $this->configValidator = new AlshayaConfigValidator();
    $this->collectedFilesList = [];
    $this->buildDir = $dir . "/build";
    $this->templateDir = $dir . "/templates";
    $this->collectionFile = $this->buildDir . DIRECTORY_SEPARATOR . self::PROFILE_COLLECTION;
  }

  /**
   * Collect yaml files for all markets and languages.
   *
   * @param bool $rebuild
   *   True to recollect files else False.
   */
  protected function collectYamlFiles($rebuild = FALSE) {
    if ($rebuild) {
      foreach ($this->alshayaMarkets as $market) {
        foreach ($this->alshayaLanguages as $language) {
          $market_common = [];

          if (file_exists($this->templateDir . "/variables/languages/$language.yml")) {
            $market_common[] = $this->templateDir . "/variables/languages/$language.yml";
          }

          if (file_exists($this->templateDir . "/variables/markets/$market.yml")) {
            $market_common[] = $this->templateDir . "/variables/markets/$market.yml";
          }
          $files["{$market}_{$language}"] = array_merge([$this->templateDir . '/variables/common.yml'], $market_common);

          $directory   = $this->templateDir . '/variables/brands';
          $brands_dirs = new \DirectoryIterator($directory);

          foreach ($brands_dirs as $brands_dir) {

            if (!$brands_dir->isDot() && $brands_dir->isDir()) {

              $current_brand                                   = $brands_dir->getBasename();
              $files["{$current_brand}_{$market}_{$language}"] = $files["{$market}_{$language}"];

              if (file_exists($brands_dir->getPathname() . "/{$current_brand}.yml")) {
                $files["{$current_brand}_{$market}_{$language}"] = array_merge(
                  $files["{$current_brand}_{$market}_{$language}"],
                  [$brands_dir->getPathname() . "/{$current_brand}.yml"]
                );
              }

              if (file_exists($brands_dir->getPathname() . "/languages/$language.yml")) {
                $files["{$current_brand}_{$market}_{$language}"] = array_merge(
                  $files["{$current_brand}_{$market}_{$language}"],
                  [$brands_dir->getPathname() . "/languages/$language.yml"]
                );
              }

              if (file_exists($brands_dir->getPathname() . "/markets/$market/$market.yml")) {
                $files["{$current_brand}_{$market}_{$language}"] = array_merge(
                  $files["{$current_brand}_{$market}_{$language}"],
                  [$brands_dir->getPathname() . "/markets/$market/$market.yml"]
                );
              }

              if (file_exists($brands_dir->getPathname() . "/markets/$market/languages/$language.yml")) {
                $files["{$current_brand}_{$market}_{$language}"] = array_merge(
                  $files["{$current_brand}_{$market}_{$language}"],
                  [$brands_dir->getPathname() . "/markets/$market/languages/$language.yml"]
                );
              }

              $env_directory = $brands_dir->getPathname() . "/env";
              $env_dirs      = new \DirectoryIterator($env_directory);

              foreach ($env_dirs as $env_dir) {
                if (!$env_dir->isDot() && $env_dir->isDir()) {
                  $current_env = $env_dir->getBasename();
                  $final_key   = "{$current_brand}-{$market}-{$current_env}-{$language}";

                  $this->collectedFilesList[$final_key] = $files["{$current_brand}_{$market}_{$language}"];
                  if (file_exists($env_dir->getPathname() . "/{$current_env}.yml")) {
                    $this->collectedFilesList[$final_key] = array_merge(
                      $files["{$current_brand}_{$market}_{$language}"],
                      [$env_dir->getPathname() . "/{$current_env}.yml"]
                    );
                  }

                  $env_lang_directory = $env_dir->getPathname() . "/languages/";
                  if (file_exists($env_lang_directory . "$language.yml")) {
                    $this->collectedFilesList[$final_key] = array_merge(
                      $this->collectedFilesList[$final_key],
                      [$env_lang_directory . "$language.yml"]
                    );
                  }

                  $env_market_directory = $env_dir->getPathname() . "/markets/$market/";
                  if (file_exists($env_market_directory . "$market.yml")) {
                    $this->collectedFilesList[$final_key] = array_merge(
                      $this->collectedFilesList[$final_key],
                      [$env_market_directory . "$market.yml"]
                    );
                  }

                  $env_market_language_directory = $env_dir->getPathname() . "/markets/$market/languages/";
                  if (file_exists($env_market_language_directory . "$language.yml")) {
                    $this->collectedFilesList[$final_key] = array_merge(
                      $this->collectedFilesList[$final_key],
                      [$env_market_language_directory . "$language.yml"]
                    );
                  }

                }
              }
            }
          }
        }
      }
      $this->dumpYaml($this->collectionFile, $this->collectedFilesList);
    }
  }

  /**
   * Collect variable files for given sites.
   *
   * @param array $specific_site
   *   Generate variables for given sites.
   * @param bool $rebuild
   *   True to recollect all the variable files else false.
   *
   * @return array|mixed
   *   Return assoc. array with profile name as key and file lists as value.
   */
  public function buildVarsForGivenSites(array $specific_site = [], $rebuild = FALSE) {
    if (empty($specific_site)) {
      return $this->getCollectedYamlFiles(TRUE);
    }

    $profiles = $this->getCollectedYamlFiles($rebuild);
    if (!empty($specific_site)) {
      $profiles = array_filter(
        $profiles,
        function ($profile) use ($specific_site) {
          $return = FALSE;
          foreach ($specific_site as $site) {
            if (strpos($profile, $site) !== FALSE) {
              $return = TRUE;
              break;
            }
          }
          return $return;
        },
        ARRAY_FILTER_USE_KEY
      );
    }

    if (!empty($profiles)) {
      return $profiles;
    }

    return $this->buildVarsForGivenSites($specific_site, TRUE);
  }

  /**
   * Get collected yaml files with profiles.
   *
   * @param bool $rebuild
   *   True to recollect all the variable files else false.
   *
   * @return array|mixed
   *   Return assoc. array with profile name as key and file lists as value.
   */
  protected function getCollectedYamlFiles($rebuild = FALSE) {
    if ($rebuild) {
      $this->collectYamlFiles($rebuild);
      return $this->collectedFilesList;
    }

    if (!file_exists($this->collectionFile)
        || (file_exists($this->collectionFile)
            && !$this->collectedFilesList = $this->getParsedContent($this->collectionFile)
        )
    ) {
      $this->collectYamlFiles(TRUE);
    }

    if (!empty($this->collectedFilesList)) {
      return $this->collectedFilesList;
    }
  }

  /**
   * Return a final array of merged keys from given yaml files.
   *
   * @param array $yaml_files
   *   List of yaml files.
   * @param string $profile
   *   (Optional) Current site profile.
   * @param bool $rebuild
   *   True to recollect all the variable files else false.
   *
   * @return array|mixed
   *   Return array.
   *
   * @throws \Exception
   */
  public function mergeYamlFiles(array $yaml_files, $profile = NULL, $rebuild = FALSE): array {
    $profile_file = $this->buildDir . DIRECTORY_SEPARATOR . "profiles/$profile.yml";
    if ($rebuild == FALSE && file_exists($profile_file)) {
      $final_yaml = $this->getParsedContent($profile_file);
      if (!empty($final_yaml)) {
        return $final_yaml;
      }
    }

    $final_yaml = ['variables' => [], 'tests' => [], 'tags' => []];
    if (!empty($profile)) {
      $final_yaml['tags'] = (array) explode('-', $profile);
    }

    if (count($yaml_files) < 2) {
      return $this->getParsedContent($yaml_files[0]);
    }

    foreach ($yaml_files as $yaml_file) {
      $yaml_parsed = $this->getParsedContent($yaml_file);

      try {
        $this->processor->processConfiguration(
          $this->configValidator,
          ['config' => $yaml_parsed]
        );
      }
      catch (\Exception $e) {
        throw new \Exception('file:' . $yaml_file . '::' . $e->getMessage());
      }

      if (!empty($yaml_parsed['variables'])) {
        $final_yaml['variables'] = array_replace_recursive($final_yaml['variables'], $yaml_parsed['variables']);
      }

      if (!empty($yaml_parsed['tags'])) {
        $final_yaml['tags'] = array_merge($final_yaml['tags'], $yaml_parsed['tags']);

        if (!empty($final_yaml['tags'])) {
          $final_yaml['tags'] = array_unique($final_yaml['tags']);
        }
      }

      if (!empty($yaml_parsed['tests'])) {
        $final_yaml['tests'] = array_merge($final_yaml['tests'], $yaml_parsed['tests']);
        if (!empty($final_yaml['tests'])) {
          $final_yaml['tests'] = array_unique($final_yaml['tests']);
        }

      }
    }

    if (!empty($profile) && empty($final_yaml['variables']['url_base_uri'])) {
      return [];
    }

    // Convert url variables to full url.
    if (!empty($final_yaml['variables']['url_base_uri'])) {
      array_walk($final_yaml['variables'], function (&$item, $key, $prefix) {
        if ($item !== $prefix && substr($key, 0, 4) === 'url_') {
          $item = $prefix . DIRECTORY_SEPARATOR . ltrim($item, '/');
        }

      }, $final_yaml['variables']['url_base_uri']);
    }

    // Moves 'tags' inside variables with '@tags' key.
    $final_yaml['variables']['@tags'] = array_map(
      function ($tag) {
        return '@' . $tag;
      },
      $final_yaml['tags']
    );
    unset($final_yaml['tags']);

    // Generate a yaml file for all collected variables.
    $this->dumpYaml($profile_file, $final_yaml);
    return $final_yaml;
  }

  /**
   * Parse content of given yaml file.
   *
   * @param string $yaml_file
   *   Convert given file content to array.
   *
   * @return array|mixed
   *   Return an array.
   */
  public function getParsedContent($yaml_file) {
    if (!file_exists($yaml_file)) {
      return [];
    }
    $content = file_get_contents($yaml_file);
    return !empty($content) ? $this->parser->parse($content) : [];
  }

  /**
   * Prepare behat.yml config from template.
   *
   * @param string $behat_template_file
   *   The path to behat template file.
   * @param array $variables
   *   Variables array that used to generate profile suites.
   * @param null|string $profile
   *   The name of the profile.
   *
   * @return array|mixed
   *   Return the array generated from behat template file.
   */
  public function prepareBehatYaml($behat_template_file, array $variables, $profile = NULL , $viewport) {
    $yaml = $this->getParsedContent($behat_template_file);
    $yaml['suites']['default']['paths'] = ["%paths.base%/build/features/$profile"];
    $brand = explode('-', $profile);
    $yaml['extensions']['Drupal\DrupalExtension']['subcontexts']['paths'] = ["%paths.base%/src/bootstrap/Drupal/$brand[0]/"];

    // Set the MinkExtension base_url to current site's base url.
    if (isset($variables['variables']['url_base_uri'])) {
      $yaml['extensions']['Behat\MinkExtension']['base_url'] = $variables['variables']['url_base_uri'];
    }

    $yaml['extensions']['Bex\Behat\ScreenshotExtension']['image_drivers'] = [
      'local' =>  [
        'screenshot_directory' => "%paths.base%/features/$profile/screenshots",
      ],
    ];

    if ($viewport == 'mobile') {
      $yaml['extensions']['Behat\MinkExtension']['selenium2']['capabilities']['chrome']['switches'] = array("--window-size=375,667");
      $yaml['suites']['default']['filters']['tags'] = "~@desktop";
    }
    else {
      $yaml['extensions']['Behat\MinkExtension']['selenium2']['capabilities']['chrome']['switches'] = array("--window-size=1440,960");
      $yaml['suites']['default']['filters']['tags'] = "~@mobile";
    }

    // Set the folder for report.
    if (!empty($profile)) {
      $yaml['formatters'] = [
        'html' => [
          'output_path' => "%paths.base%/features/$profile/reports/html/behat",
        ],
      ];
    }
    return $yaml;
  }

  /**
   * Dump given content to given file.
   *
   * @param string $new_file_path
   *   The path to yaml file that needs to be generated.
   * @param array $content
   *   The array of content that needs to be written in the file.
   * @param bool $append
   *   True if the file in append mode.
   * @param null|string $key
   *   (Optional) key if $content requires to be assigned with given key.
   */
  public function dumpYaml($new_file_path, array $content, $append = FALSE, $key = NULL) {
    if (!file_exists($new_file_path)) {
      $pathinfo = pathinfo($new_file_path);
      if (!is_dir($pathinfo['dirname'])) {
        mkdir($pathinfo['dirname'], 0777, TRUE);
      }
    }

    $content = !empty($key) ? [$key => $content] : $content;
    $yaml = $this->dumper->dump($content, 10);

    if (!$append) {
      file_put_contents($new_file_path, $yaml);
    }
    else {
      file_put_contents($new_file_path, $yaml, FILE_APPEND | LOCK_EX);
    }
  }

}
