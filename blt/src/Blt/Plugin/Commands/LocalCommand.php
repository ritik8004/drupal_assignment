<?php

namespace Alshaya\Blt\Plugin\Commands;

use Acquia\Blt\Robo\BltTasks;
use Robo\Contract\VerbosityThresholdInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Defines commands in the "local" namespace.
 */
class LocalCommand extends BltTasks {

  /**
   * Mapping of stack aliases.
   *
   * @var array
   */
  protected $stacks = [
    1 => 'alshaya.01',
    2 => 'alshaya2.02',
    3 => 'alshaya3bis.01',
    4 => 'alshaya4.04',
    5 => 'alshaya5.05',
    7 => 'alshaya7tmp.07',
    8 => 'alshayadc1.02',
  ];

  /**
   * Environments list.
   *
   * @var string[]
   */
  protected $envs = [
    'dev',
    'dev2',
    'dev3',
    'qa2',
    'test',
    'uat',
    'pprod',
  ];

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
    // Reset local settings file.
    $this->invokeCommand('local:reset-local-settings');

    if (is_numeric(substr($env, 0, 2))) {
      $this->yell('Please remove stack numbers, code is now upgraded to take care of it. Just say test / uat / dev2 / etc.');
      return;
    }

    $info = $this->validateAndPrepareInfo($site, $env);

    if (empty($info)) {
      return;
    }

    $this->say('Refreshing local from server, info below.');
    $this->say('Local: ' . $info['local']['url']);
    $this->say('Remote: ' . $info['remote']['url']);

    // If the mode is set to download or archive doesn't exist we download
    // the dump from cloud.
    if ($mode === 'download' || !file_exists($info['archive'])) {
      if (!$this->downloadDb($site, $env)) {
        return;
      }
    }

    $this->say('Dropping local database');
    $this->taskDrush()
      ->drush('sql-drop')
      ->alias($info['local']['alias'])
      ->uri($info['local']['url'])
      ->run();

    $this->say('Importing database from remote');
    $this->taskDrush()
      ->drush('sql-cli < ' . $info['archive'])
      ->alias($info['local']['alias'])
      ->uri($info['local']['url'])
      ->run();

    if (getenv('LANDO')) {
      // If we're running Lando, our best option is to flush our memcache
      // services.
      $this->say('Flushing memcache servers.');
      $this->_exec('echo "flush_all" | nc -q 2 memcache 11211');
    }
    else {
      $this->say('Restarting memcache service');
      $this->taskDrush()
        ->drush('ssh')
        ->arg('sudo service memcached restart')
        ->alias($info['local']['alias'])
        ->uri($info['local']['url'])
        ->run();
    }

    $this->taskDrush()
      ->drush('status')
      ->alias($info['local']['alias'])
      ->uri($info['local']['url'])
      ->printOutput(FALSE)
      ->run();

    $this->say('Disable cloud modules');
    $this->taskDrush()
      ->drush('pmu purge acquia_connector shield dblog datadog_js')
      ->alias($info['local']['alias'])
      ->uri($info['local']['url'])
      ->run();

    $modules_to_enable = 'syslog field_ui views_ui restui stage_file_proxy';

    $this->say('Enable local only modules');
    $this->taskDrush()
      ->drush('en ' . $modules_to_enable)
      ->alias($info['local']['alias'])
      ->uri($info['local']['url'])
      ->run();

    $this->say('Configure stage_file_proxy');
    $this->taskDrush()
      ->drush('cset')
      ->arg('stage_file_proxy.settings')
      ->arg('origin')
      ->arg($info['origin'])
      ->alias($info['local']['alias'])
      ->uri($info['local']['url'])
      ->run();

    $this->taskDrush()
      ->drush('cset')
      ->arg('stage_file_proxy.settings')
      ->arg('origin_dir')
      ->arg($info['origin_dir'] . '/files')
      ->alias($info['local']['alias'])
      ->uri($info['local']['url'])
      ->run();

    // Now the last thing, dev script, I love it :).
    $dev_script_path = $this->getConfigValue('repo.root') . '/scripts/install-site-dev.sh';
    if (file_exists($dev_script_path)) {
      $this->_exec('sh ' . $dev_script_path . ' ' . $info['local']['url']);
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

    if (strpos($env, 'live') > -1) {
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
      ->rawArg(' > ' . $info['archive'] . '.gz --gzip');

    $result = $task->run();

    if ($result->wasSuccessful()) {
      $gunzip = $this->taskExec('gunzip -f ' . $info['archive'] . '.gz');
      $gunzip->run();

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
      $path = $this->getConfigValue('repo.root') . '/files-private';

      if (!file_exists($path)) {
        $this->say('Creating temp directory at: ' . $path);
        $taskFilesystemStack = $this->taskFilesystemStack();
        $taskFilesystemStack->mkdir($path);
      }

      // Ensure the directory is writable.
      $taskFilesystemStack = $this->taskFilesystemStack();
      $taskFilesystemStack->stopOnFail()
        ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
        ->chmod($path, 0755);
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
    $remote_site = $site;
    $site = preg_replace('/\d/', '', $site);
    static $static;

    if (isset($static[$env][$site])) {
      return $static[$env][$site];
    }

    $data = Yaml::parse(file_get_contents($this->getConfigValue('repo.root') . '/blt/alshaya_sites.yml'));
    $sites = $data['sites'];

    if (empty($site) || empty($sites[$site])) {
      $this->yell('Empty or invalid site code. You probably need some sleep :)', 40, 'red');
      return 0;
    }

    $site_data = $sites[$site];

    $info = [];

    $info['profile'] = $site_data['type'];

    $info['local']['url'] = CustomCommand::getSiteUri($site);
    $info['local']['alias'] = 'self';

    foreach ($this->stacks as $alias_prefix) {
      $info['remote']['alias'] = $alias_prefix . $env;

      // Get remote data to confirm site code is valid and we can get db role
      // and remote url.
      $remote_data = $this->getSitesData($info['remote']['alias']);

      $info['remote']['db_role'] = $this->extractInfo($remote_data, $remote_site, 'db_role');
      if (empty($info['remote']['db_role'])) {
        continue;
      }
      $info['remote']['url'] = $this->extractInfo($remote_data, $remote_site, 'url');
      $info['origin_dir'] = 'sites/g/files/' . $info['remote']['db_role'];
      $info['origin'] = 'https://' . $info['remote']['url'];
      break;
    }

    if (empty($info['remote']['db_role']) || empty($info['remote']['url'])) {
      $this->yell('Site seems not installed on requested env or you do not have access to this env, check again please.', 40, 'red');
      return 0;
    }

    $info['archive'] = $this->tempDir() . "/alshaya_${site}_${env}.sql";

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
    $task = $this->taskDrush();
    $task->interactive(FALSE);

    $task->drush('acsf-tools-list')
      ->alias($remote_alias)
      ->option('fields', 'name,domains')
      ->printOutput(FALSE);
    $result = $task->run();
    $message = $result->getMessage();
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
        if (!str_starts_with($line, ' ')) {
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

  /**
   * Pause ACM Queue for unavailable sites on particular ENV.
   *
   * @param string $env
   *   Environment code.
   *
   * @command local:pause-unavailable
   *
   * @description Pause ACM Queue for unavailable sites on particular ENV.
   */
  public function pauseUnavailableSites(string $env) {
    $data = $this->getSitesData('mckw.01' . $env);
    $sites = [];
    foreach ($data ?? [] as $line) {
      if (strpos($line, ' ') < -1) {
        $sites[] = $line;
      }
    }

    // First pause for all the sites in particular ENV.
    $this->_exec('php tests/apis/conductor_v2/pauseQueues.php ' . $env . ' all all pause');

    // Unpause for the sites which are currently available.
    // We do so as we don't have a way to know current status of queue.
    foreach ($sites as $site) {
      $country = substr($site, -2);
      $brand = substr($site, 0, -2);
      $this->_exec("php tests/apis/conductor_v2/pauseQueues.php $env $brand $country resume");
    }
  }

  /**
   * Execute drush command on all sites on all stacks of given ENV.
   *
   * @param string $env
   *   Environment code.
   * @param string $drush_command
   *   Drush Command.
   *
   * @command cloud:drush
   *
   * @usage drush cloud:drush uat crf
   *   Execute drush crf on all the sites of UAT environment.
   *
   * @description Execute drush command on all sites on all stacks of given ENV.
   */
  public function cloudDrush(string $env, string $drush_command) {
    if (!in_array($env, $this->envs)) {
      $this->yell('Invalid environment name.');
      return;
    }

    foreach ($this->stacks as $stack) {
      $task = $this->taskDrush();
      $task->interactive(FALSE);
      $task->drush('sfl --fields')
        ->alias($stack . $env)
        ->printOutput(FALSE);
      $sites = $task->run();
      foreach (explode(PHP_EOL, $sites->getMessage()) as $site) {
        $task = $this->taskDrush();
        $task->interactive(FALSE);
        $task->drush($drush_command)
          ->uri("${site}-${env}.factory.alshaya.com")
          ->alias($stack . $env);
        $task->run();
      }
    }
  }

  /**
   * Add new QA account.
   *
   * @param string $mail
   *   Mail address of QA.
   *
   * @command local:add-qa-account
   *
   * @description Add QA account for creation on restaging on all non-prod envs.
   */
  public function addQaAccount(string $mail) {
    foreach ($this->envs as $env) {
      // Dev and Test use same server so skip it for one.
      if ($env === 'test') {
        continue;
      }

      foreach ($this->stacks as $stack) {
        $this->io()->writeln("$env - $stack");
        $task = $this->taskDrush();
        $task->interactive(FALSE);
        $task->drush('ssh "echo ' . $mail . ' >> ~/qa_accounts.txt"')
          ->alias($stack . $env)
          ->printOutput(TRUE);
        $result = $task->run();
        $this->io()->writeln($result->getMessage());
      }
    }
  }

  /**
   * Clear QA accounts file.
   *
   * @command local:clear-qa-accounts
   *
   * @description Clear QA accounts file on all non-prod envs.
   */
  public function clearQaAccounts() {
    foreach ($this->envs as $env) {
      // Dev and Test use same server so skip it for one.
      if ($env === 'test') {
        continue;
      }

      foreach ($this->stacks as $stack) {
        $this->io()->writeln("$env - $stack");
        $task = $this->taskDrush();
        $task->interactive(FALSE);
        $task->drush('ssh "rm ~/qa_accounts.txt"')
          ->alias($stack . $env)
          ->printOutput(TRUE);
        $result = $task->run();
        $this->io()->writeln($result->getMessage());
      }
    }
  }

}
