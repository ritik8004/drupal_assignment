<?php

$options = getopt("", ["rebuild::", "site::"]);

//Create a variable for start time
$time_start = microtime(true);

use Alshaya\BehatBuild\AlshayaFeatureProcess;
use Alshaya\BehatBuild\AlshayaYamlProcess;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\Output;

define('BEHAT_BIN_PATH', __FILE__);
define('TEMPLATE_DIR', __DIR__ . "/templates");
define('BUILD_DIR', __DIR__ . "/build");

require_once getcwd() . '/vendor/autoload.php';

$rebuild = !empty($options['rebuild']) ? $options['rebuild'] : false;
$specific_site = !empty($options['site']) ? explode(',', $options['site']) : [];

$behat = new AlshayaYamlProcess(__DIR__);
$behat_config = [];
if (!empty($specific_site) && $rebuild == FALSE) {
  $behat_config = $behat->getParsedContent(BUILD_DIR . '/profiles.yml');
}
$profiles = $behat->buildVarsForGivenSites($specific_site, $rebuild);

$viewports = array('desktop', 'mobile');

foreach ($profiles as $profile => $files) {
  $variables = $behat->mergeYamlFiles($files, $profile, $rebuild);
  if (isset($variables['variables']['url_base_uri'])) {
    foreach ($viewports as $key => $viewport) {
      if (!is_dir(BUILD_DIR)) {
        mkdir(BUILD_DIR);
      }

      if (is_dir(BUILD_DIR)) {
        $prepare_behat = $behat->prepareBehatYaml(TEMPLATE_DIR . '/behat.yml', $variables, $profile, $viewport);

        $output = new ConsoleOutput();
        $output->write("Building features for: $profile", TRUE, Output::VERBOSITY_NORMAL);

        $behat_config[$profile . '-' . $viewport] = $prepare_behat;
        $feature = new AlshayaFeatureProcess([
          'site' => $profile,
          'variables' => $variables['variables'] ?? [],
          'features' => $variables['tests'] ?? [],
          'template_path' => TEMPLATE_DIR . '/features',
          'build_path' => BUILD_DIR . '/features',
          'viewport' => $viewport
        ]);
        $feature->generateFeatureFiles();
      }
    }
  }
}
$behat->dumpYaml(BUILD_DIR . '/profiles.yml', $behat_config);
