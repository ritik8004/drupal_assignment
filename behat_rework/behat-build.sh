#!/usr/bin/env php
<?php

use Alshaya\BehatBuild\AlshayaFeatureProcess;
use Alshaya\BehatBuild\AlshayaYamlProcess;

define('BEHAT_BIN_PATH', __FILE__);
define('TEMPLATE_DIR', __DIR__ . "/templates");
define('BUILD_DIR', __DIR__ . "/build");

require_once getcwd() . '/vendor/autoload.php';

$behat = new AlshayaYamlProcess();
$behat->collectYamlFiles();
$i = 0;

foreach ($behat->getCollectedYamlFiles() as $profile => $files) {
  $variables = $behat->mergeYamlFiles($files);
  if (isset($variables['variables']['base_url'])) {
    $prepare_behat = $behat->prepareBehatYaml(TEMPLATE_DIR . '/behat.yml', $variables, $profile);
    $behat->dumpYaml(BUILD_DIR . '/brands.yml', ($i > 0), $prepare_behat, $profile);
    $i++;

    $base_path = getcwd() . DIRECTORY_SEPARATOR;
    $feature = new AlshayaFeatureProcess([
      'site' => $profile,
      'variables' => $variables['variables'] ?? [],
      'features' => $variables['tests'] ?? [],
      'features' => $variables['tests'] ?? [],
      'template_path' => TEMPLATE_DIR . '/features',
      'build_path' => BUILD_DIR . '/features'
    ]);
    $feature->generateFeatureFiles();
  }
}
