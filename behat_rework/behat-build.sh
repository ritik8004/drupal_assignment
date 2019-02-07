#!/usr/bin/env php
<?php

require __DIR__ . "/build.php";
die();

#$options = getopt("b::m::e::l", ["brand::", "market::", "env::", "lang::"]);

use Alshaya\BehatBuild\AlshayaFeatureProcess;
use Alshaya\BehatBuild\AlshayaYamlProcess;

define('BEHAT_BIN_PATH', __FILE__);
define('TEMPLATE_DIR', __DIR__ . "/templates");
define('BUILD_DIR', __DIR__ . "/build");

require_once getcwd() . '/vendor/autoload.php';

$behat = new AlshayaYamlProcess($config, $profile);
$profiles = $behat->getCollectedYamlFiles();
$i = 0;





foreach ($profiles as $profile => $files) {
  $variables = $behat->mergeYamlFiles($files, $profile);
  if (isset($variables['variables']['var_base_url'])) {
    if (!is_dir(BUILD_DIR)) {
      mkdir(BUILD_DIR);
    }

    if (is_dir(BUILD_DIR)) {
      $prepare_behat = $behat->prepareBehatYaml(TEMPLATE_DIR . '/behat.yml', $variables, $profile);
      $behat->dumpYaml(BUILD_DIR . '/profiles.yml', $prepare_behat, ($i > 0), $profile);
      $i++;

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
}
