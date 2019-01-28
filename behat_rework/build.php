<?php

define('BEHAT_BIN_PATH', __FILE__);
define('TEMPLATE_DIR', __DIR__ . "/templates");
define('BUILD_DIR', __DIR__ . "/build");

if (!class_exists('Behat\Behat\ApplicationFactory', true)) {
  if (is_file($autoload = __DIR__ . '/../vendor/autoload.php')) {
    require($autoload);
  } elseif (is_file($autoload = __DIR__ . '/../../../autoload.php')) {
    require($autoload);
  } else {
    fwrite(STDERR,
      'You must set up the project dependencies, run the following commands:'.PHP_EOL.
      'curl -s http://getcomposer.org/installer | php'.PHP_EOL.
      'php composer.phar install'.PHP_EOL
    );
    exit(1);
  }
}

if (is_file($autoload = getcwd() . '/vendor/autoload.php')) {
  require $autoload;
}

require __DIR__ . '/AlshayaBehatBase.php';

$behat = new AlshayaBehatBase();
$behat->collectYamlFiles();
$i = 0;
foreach ($behat->getCollectedYamlFiles() as $profile => $files) {
  $variables = $behat->mergeYamlFiles($files);
  if (isset($variables['variables']['base_url'])) {
    $prepare_behat = $behat->parePareBehatYaml(TEMPLATE_DIR . '/behat.yml', $variables, $profile);
    $behat->dumpYaml(BUILD_DIR . '/brands.yml', ($i > 0), $prepare_behat, $profile);
    $i++;
  }
}


//Generate json params
//  $params =
//    [ 'extensions' =>
//        [
//          'Behat\MinkExtension' =>
//            [
//              'base_url' => 'https://' . $row[1]
//            ],
//          "emuse\BehatHTMLFormatter\BehatHTMLFormatterExtension" =>
//            [
//              "file_name" => $row[0]
//            ]
//        ]
//    ];
//  $params = json_encode($params);
//  //Export json Params and run behat test
//  $json = "export BEHAT_PARAMS='" . $params . "'";
//  echo exec($json .  "&& bin/behat");
