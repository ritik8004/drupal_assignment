<?php

namespace Acquia\Blt\Custom\Commands;

use Acquia\Blt\Robo\BltTasks;
use Symfony\Component\Yaml\Yaml;

/**
 * Defines commands in the "local" namespace.
 */
class LocalCommand extends BltTasks {

  /**
   * Sync DB from remote and set stage file proxy.
   *
   * @param string $site
   *   Site code.
   * @param string $env
   *   Environment code.
   * @param string $mode
   *   Mode => download/reuse.
   *
   * @command local:sync
   *
   * @description Syncs DB from remote and set stage file proxy.
   */
  public function localSync($site = '', $env = 'dev', $mode = 'reuse') {
    $info = $this->validateAndPrepareInfo($site, $env);

    if (empty($info)) {
      return;
    }

    $this->say('Refreshing local from server, info below.');
    $this->say('Local: ' . $info['local']['url']);
    $this->say('Remote: ' . $info['remote']['url']);

    if ($mode === 'download') {
      if (!$this->downloadDb($site, $env)) {
        return;
      }
    }

    $this->say('Dropping local database');
    $this->taskDrush()
      ->drush('sql-drop')
      ->alias($info['local']['alias'])
      ->uri($info['local']['url'])
      ->assume(TRUE)
      ->run();

    $this->say('Importing database from remote');
    $this->taskDrush()
      ->drush('sql-cli < ' . $info['archive'])
      ->alias($info['local']['alias'])
      ->uri($info['local']['url'])
      ->run();

    $this->say('Restarting memcache service');
    $this->taskDrush()
      ->drush('ssh')
      ->arg('sudo service memcached restart')
      ->alias($info['local']['alias'])
      ->uri($info['local']['url'])
      ->run();

    $this->say('Disable cloud modules');
    $this->taskDrush()
      ->drush('pmu purge alshaya_search_acquia_search acquia_search acquia_connector shield')
      ->assume(TRUE)
      ->alias($info['local']['alias'])
      ->uri($info['local']['url'])
      ->run();

    $modules_to_enable = 'dblog field_ui views_ui features_ui restui stage_file_proxy';
    if ($info['profile'] == 'alshaya_transac') {
      $modules_to_enable .= ' alshaya_search_local_search';
    }

    $this->say('Enable local only modules');
    $this->taskDrush()
      ->drush('en ' . $modules_to_enable)
      ->assume(TRUE)
      ->alias($info['local']['alias'])
      ->uri($info['local']['url'])
      ->run();

    // @TODO Remove this and alshaya_search_* modules after we use new approach.
    if ($info['profile'] == 'alshaya_transac') {
      $this->say('Save server config again to ensure local solr is used.');
      $this->taskDrush()
        ->drush('php-eval')
        ->arg("alshaya_config_install_configs(['search_api.server.acquia_search_server'], 'alshaya_search');")
        ->assume(TRUE)
        ->alias($info['local']['alias'])
        ->uri($info['local']['url'])
        ->run();

      $this->say('Clear solr index');
      $this->taskDrush()
        ->drush('search-api-clear')
        ->arg('acquia_search_index')
        ->assume(TRUE)
        ->alias($info['local']['alias'])
        ->uri($info['local']['url'])
        ->run();
    }

    $this->say('Reset super admin account');
    $this->taskDrush()
      ->drush('sqlq')
      ->arg("update users_field_data set mail = 'no-reply@acquia.com', name = 'admin' where uid = 1")
      ->assume(TRUE)
      ->alias($info['local']['alias'])
      ->uri($info['local']['url'])
      ->run();

    $this->taskDrush()
      ->drush('user-password')
      ->arg('admin')
      ->option('password', 'admin')
      ->assume(TRUE)
      ->alias($info['local']['alias'])
      ->uri($info['local']['url'])
      ->run();

    $this->taskDrush()
      ->drush('uublk')
      ->option('name', 'admin')
      ->assume(TRUE)
      ->alias($info['local']['alias'])
      ->uri($info['local']['url'])
      ->run();

    $this->say('Configure stage_file_proxy');
    $this->taskDrush()
      ->drush('cset')
      ->arg('stage_file_proxy.settings')
      ->arg('origin')
      ->arg($info['origin'])
      ->assume(TRUE)
      ->alias($info['local']['alias'])
      ->uri($info['local']['url'])
      ->run();

    $this->taskDrush()
      ->drush('cset')
      ->arg('stage_file_proxy.settings')
      ->arg('origin_dir')
      ->arg($info['origin_dir'])
      ->assume(TRUE)
      ->alias($info['local']['alias'])
      ->uri($info['local']['url'])
      ->run();

    $this->taskDrush()
      ->drush('cset')
      ->arg('stage_file_proxy.settings')
      ->arg('verify')
      ->arg(0)
      ->assume(TRUE)
      ->alias($info['local']['alias'])
      ->uri($info['local']['url'])
      ->run();

    $this->say('Finally clear cache once');
    $this->taskDrush()
      ->drush('cr')
      ->alias($info['local']['alias'])
      ->uri($info['local']['url'])
      ->run();

    // Now the last thing, dev script, I love it :).
    $dev_script_path = 'scripts/install-site-dev.sh';
    if (file_exists($dev_script_path)) {
      $this->_exec('sh ' . $dev_script_path . ' ' . $site);
    }

  }

  /**
   * Sync DB from remote and set stage file proxy.
   *
   * @param string $site
   *   Site code. Provide comma separated site codes to download multiple.
   * @param string $env
   *   Environment code.
   *
   * @return bool
   *   True if download successful.
   *
   * @command local:download
   *
   * @description Syncs DB from remote and set stage file proxy.
   */
  public function downloadDb($site = '', $env = 'dev') {
    // Download multiple sites together.
    $sites = explode(',', $site);
    if (count($sites) > 1) {
      $return = TRUE;

      foreach ($sites as $site) {
        $return = $return && $this->downloadDb($site, $env);
      }

      return $return;
    }

    $info = $this->validateAndPrepareInfo($site, $env);

    if ($env == 'live') {
      $this->say('=========================');
      $this->say('=========================');
      $this->say('Please DO NOT SLOW DOWN THE PRODUCTION SERVER');
      $this->say('Go get the dump from Cloud or from someone who has access and put that in ../tmp directory, save it as alshaya_[SITE]_[ENV].sql, use reuse after that.');
      $this->say('=========================');
      $this->say('=========================');
      throw new \Exception('Downloading LIVE DB through drush sql-dump is BAD, REALLY BAD');
    }

    if (empty($info)) {
      return FALSE;
    }

    $this->say('Downloading database from ' . $info['remote']['url']);

    $task = $this->taskDrush()
      ->drush('sql-dump')
      ->alias($info['remote']['alias'])
      ->uri($info['remote']['url'])
      ->rawArg(' > ' . $info['archive']);

    $result = $task->run();

    if ($result->wasSuccessful()) {
      $this->say('Download complete.');
      return TRUE;
    }

    $this->yell('Download failed.', 40, 'red');
    return FALSE;
  }

  /**
   * Returns the temp directory path.
   *
   * Creates if required.
   *
   * @return string
   *   Temp directory path.
   */
  private function tempDir() {
    static $path;

    if (!isset($path)) {
      $path = realpath('..') . '/tmp';

      if (!file_exists($path)) {
        $this->say('Creating temp director at: ' . $path);
        $taskFilesystemStack = $this->taskFilesystemStack();
        $taskFilesystemStack->mkdir($path);
      }
    }

    return $path;
  }

  /**
   * Helper function to validate and prepare info required to sync.
   *
   * @param string $site
   *   Site code.
   * @param string $env
   *   Environment code.
   *
   * @return array|int
   *   Fully prepared array or 0.
   */
  private function validateAndPrepareInfo($site, $env) {
    static $static;

    if (isset($static[$env][$site])) {
      return $static[$env][$site];
    }

    $sites = $this->getConfig()->get('sites');

    if (empty($site) || empty($sites[$site])) {
      $this->yell('Empty or invalid site code. You probably need some sleep :)', 40, 'red');
      return 0;
    }

    $info = [];

    $info['profile'] = $sites[$site]['type'];

    $info['local']['url'] = 'local.alshaya-' . $site . '.com';
    $info['local']['alias'] = 'alshaya.local';
    $info['remote']['alias'] = 'alshaya.01' . $env;

    // Get remote data to confirm site code is valid and we can get db role
    // and remote url.
    $remote_data = $this->getSitesData($info['remote']['alias']);

    $info['remote']['db_role'] = $this->extractInfo($remote_data, $site, 'db_role');
    $info['remote']['url'] = $this->extractInfo($remote_data, $site, 'url');

    $info['origin_dir'] = 'sites/g/files/' . $info['remote']['db_role'] . '/files/';
    $info['origin'] = 'https://' . $info['remote']['url'];

    if (empty($info['remote']['db_role']) || empty($info['remote']['url'])) {
      $this->yell('Site seems not installed on requested env or you do not have access to this env, check again please.', 40, 'red');
      return 0;
    }

    $info['archive'] = realpath('..') . "/tmp/alshaya_${site}_${env}.sql";

    $static[$env][$site] = $info;

    return $info;
  }

  /**
   * Helper function to get sites mapping data from server.
   *
   * @param string $remote_alias
   *   Server alias to get the data from.
   *
   * @return array
   *   Server response.
   */
  private function getSitesData($remote_alias) {
    // This file will allow execution for people without drush access to cloud.
    $path = $this->tempDir() . '/sites-' . $remote_alias . '.data';

    if (file_exists($path)) {
      $message = file_get_contents($path);
    }
    else {
      $task = $this->taskDrush()
        ->drush('acsf-tools-list')
        ->alias($remote_alias)
        ->option('fields', 'name,domains')
        ->printOutput(FALSE);

      $result = $task->run();

      $message = $result->getMessage();

      file_put_contents($path, $message);
    }

    if (empty($message)) {
      return [];
    }

    return explode(PHP_EOL, $message);
  }

  /**
   * Helper function to extract required info from server response.
   *
   * @param array $data
   *   Data/ Response from server.
   * @param string $site
   *   Site for which we want to extract the data.
   * @param string $info_required
   *   Key for the information required from data.
   *
   * @return string
   *   Value for requested info from Data.
   */
  private function extractInfo(array $data, $site, $info_required) {
    $yaml_data = '';

    $start_reading = FALSE;

    foreach ($data as $line) {
      if ($line == $site) {
        $start_reading = TRUE;
        continue;
      }

      if ($start_reading) {
        if (strpos($line, ' ') !== 0) {
          break;
        }

        $yaml_data .= substr($line, 2) . PHP_EOL;
      }
    }

    $array = Yaml::parse($yaml_data);

    if (is_array($array)) {
      if ($info_required == 'db_role') {
        return $array['name'];
      }
      elseif ($info_required == 'url') {
        return reset($array['domains']);
      }
    }

    return '';
  }

}
