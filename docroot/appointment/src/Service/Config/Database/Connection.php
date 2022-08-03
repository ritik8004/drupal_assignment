<?php

namespace App\Service\Config\Database;

use Doctrine\Common\EventManager;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Connection as DoctrineConnection;

/**
 * Helper class for doctrine connections.
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
    require_once $params['path'] . '/../factory-hooks/pre-sites-php/local_sites.php';

    $env = alshaya_get_site_environment();
    if ($env === 'local' || $env === 'travis') {
      // @codingStandardsIgnoreLine
      global $host_site_code;

      // @todo Configure acsf database, same as configured for drupal.
      // We set "drupal" as a default database if no domain found. This assures
      // it won't throw errors on empty --uri parameter
      $params['dbname'] = $host_site_code == 'default_local' ? 'drupal' : 'drupal_alshaya_' . str_replace('-', '_', $host_site_code);

      $params['host'] = getenv('LANDO') ? 'database' : 'localhost';
    }
    else {
      // Get database settings for acsf.
      require_once $params['path'] . 'sites/g/sites.inc';
      $host = rtrim($_SERVER['HTTP_HOST'], '.');
      $data = gardens_site_data_refresh_one($host);
      $site_settings = $data['gardens_site_settings'];
      $_acsf_include_file = "/var/www/site-php/{$site_settings['site']}.{$site_settings['env']}/D8-{$site_settings['env']}-{$site_settings['conf']['acsf_db_name']}-settings.inc";

      if (file_exists($_acsf_include_file)) {
        $db_con_info = [
          '"database"' => '',
          '"username"' => '',
          '"password"' => '',
          '"host"' => '',
          '"port"' => '',
        ];

        // Read the file and get db info.
        $fn = fopen($_acsf_include_file, 'r');
        while (!feof($fn)) {
          $result = fgets($fn);
          foreach ($db_con_info as $key => $val) {
            if (str_contains($result, $key)) {
              $result = trim(str_replace(['"', ','], ['', ''], $result));
              $info = explode('=>', $result);
              $db_con_info[$key] = trim($info[1]);
            }
          }
        }

        // Close the connection.
        fclose($fn);

        // phpcs:ignore
        $params['dbname'] = $db_con_info['"database"'];
        $params['user'] = $db_con_info['"username"'];
        $params['password'] = $db_con_info['"password"'];
        $params['host'] = $db_con_info['"host"'];
        $params['port'] = $db_con_info['"port"'];
      }
    }

    parent::__construct($params, $driver, $config, $eventManager);
  }

}
