<?php

namespace App\Service\Config\Database;

use Doctrine\Common\EventManager;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Connection as DoctrineConnection;

/**
 * Class Connection.
 *
 * @package App\Service\Config\Database
 */
class Connection extends DoctrineConnection {

  /**
   * Connection constructor.
   *
   * @param array $params
   *   The connection parameters.
   * @param \Doctrine\DBAL\Driver $driver
   *   The driver to use.
   * @param \Doctrine\DBAL\Configuration|null $config
   *   (Optional) The configuration.
   * @param \Doctrine\Common\EventManager|null $eventManager
   *   (Optional) The event manager.
   *
   * @throws \Doctrine\DBAL\DBALException
   */
  public function __construct(array $params, Driver $driver, ?Configuration $config = NULL, ?EventManager $eventManager = NULL) {
    // Get site environment.
    require_once $params['path'] . '/../factory-hooks/environments/environments.php';
    $env = alshaya_get_site_environment();
    if ($env === 'local') {
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
      // We set "drupal" as a default database if no domain found. This assures
      // it won't throw errors on empty --uri parameter
      $params['dbname'] = $host_site_code == 'default_local' ? 'drupal' : 'drupal_alshaya_' . str_replace('-', '_', $host_site_code);
    }
    else {
      // Get database settings for acsf.
      require $params['path'] . '/sites/g/sites.inc';
      $host = rtrim($_SERVER['HTTP_HOST'], '.');
      $data = gardens_site_data_refresh_one($host);
      $site_settings = $data['gardens_site_settings'];
      $_acsf_include_file = "/var/www/site-php/{$site_settings['site']}.{$site_settings['env']}/D8-{$site_settings['env']}-{$site_settings['conf']['acsf_db_name']}-settings.inc";

      if (file_exists($_acsf_include_file)) {
        include $_acsf_include_file;
        // @codingStandardsIgnoreLine
        $db = $databases['default']['default'];
        $params['dbname'] = $db['database'];
        $params['user'] = $db['username'];
        $params['password'] = $db['password'];
        $params['host'] = $db['host'];
        $params['port'] = $db['port'];
      }
    }

    parent::__construct($params, $driver, $config, $eventManager);
  }

}
