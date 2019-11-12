<?php

namespace App\Service\Config\Database;

use Doctrine\Common\EventManager;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Driver;

class Connection extends \Doctrine\DBAL\Connection {

  public function __construct(array $params, Driver $driver, ?Configuration $config = NULL, ?EventManager $eventManager = NULL) {

    if (!empty($_SERVER['HTTP_HOST'])) {
      $hostname_parts = explode('.', $_SERVER['HTTP_HOST']);
      $host_site_code = str_replace('alshaya-', '', $hostname_parts[1]);
    }
    else {
      $host_site_code = 'default_local';
      foreach ($_SERVER['argv'] as $arg) {
        preg_match('/[\\S|\\s|\\d|\\D]*local.alshaya-(\\S*).com/', $arg, $matches);
        if (!empty($matches)) {
          $host_site_code = $matches[1];
          break;
        }
      }
    }

    // @todo: Configure acsf database, same as configured for drupal.
    // We set "drupal" as a default database if no domain found. This assures it won't throw errors on empty --uri parameter
    $params['dbname'] = ( $host_site_code == 'default_local' ? 'drupal' : 'drupal_alshaya_' . str_replace('-', '_', $host_site_code) );
    parent::__construct($params, $driver, $config, $eventManager);
  }

}
