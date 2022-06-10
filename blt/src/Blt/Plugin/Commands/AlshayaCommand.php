<?php

namespace Alshaya\Blt\Plugin\Commands;

use Acquia\Blt\Robo\BltTasks;
use Consolidation\AnnotatedCommand\CommandData;
use AcquiaCloudApi\Connector\Client;
use AcquiaCloudApi\Connector\Connector;

/**
 * This class defines hooks.
 */
class AlshayaCommand extends BltTasks {

  /**
   * This will be called post `git:pre-commit` command is executed.
   *
   * @hook post-command internal:git-hook:execute:pre-commit
   */
  public function postGitPreCommit($result, CommandData $commandData) {
    $arguments = $commandData->arguments();
    if (!empty($arguments['changed_files'])) {
      $this->invokeCommand('tests:yaml:lint:files:paragraph', ['file_list' => $arguments['changed_files']]);
      $this->invokeCommand('validate:phpcs:files', ['file_list' => $arguments['changed_files']]);
    }

    $failed = FALSE;
    $files = explode(PHP_EOL, $arguments['changed_files']);

    $patterns = [];

    $ignoredDirs = ['alshaya_react', 'js', 'dist', 'node_modules', '__tests__'];

    $reactDir = $this->getConfigValue('docroot') . '/modules/react';

    foreach (new \DirectoryIterator($reactDir) as $subDir) {
      if ($subDir->isDir()
        && strpos($subDir->getBasename(), '.') === FALSE
        && !in_array($subDir->getBasename(), $ignoredDirs)) {
        $pattern = '/react/' . $subDir->getBasename() . '/js';

        // For module like alshaya_algolia_react we have react files in src.
        if (is_dir($subDir->getRealPath() . '/js/src')) {
          $pattern .= '/src';
        }

        $patterns[] = $pattern;
      }
    }

    // Validate utility files.
    $patterns[] = '/react/js';

    $do_test = FALSE;

    foreach ($files as $file) {
      if (!$do_test && strpos($file, '/alshaya_spc/js/') !== FALSE) {
        $do_test = TRUE;
      }

      foreach ($patterns as $pattern) {
        if (strpos($file, $pattern) !== FALSE) {
          $paths = explode('react/', $file, 2);
          $output = $this->_exec('cd ' . $paths[0] . 'react; npm run lint ' . $paths[1]);
          if ($output->getExitCode() !== 0) {
            $failed = TRUE;
          }
        }
      }
    }

    if ($failed) {
      throw new \Exception('Please fix eslint errors described above.');
    }

    // JS Tests.
    if ($do_test) {
      $output = $this->_exec('cd ' . $reactDir . '; npm test');
      if ($output->getExitCode() !== 0) {
        $failed = TRUE;
      }
    }

    if ($failed) {
      throw new \Exception('Please fix failing tests.');
    }
  }

  /**
   * Get cloud deployment task details for application.
   *
   * @param string $applicationUuid
   *   Acquia Cloud application UUID.
   * @param string $tag
   *   Tag we need to search for.
   * @param string $status
   *   Status of deployment task.
   *
   * @command cloud:get-application-task
   *
   * @description Get cloud deployment task details for application with tag.
   *
   * @throws \Exception
   */
  public function getCloudTask($applicationUuid, $tag, $status = 'in_progress') {
    $_clientId = '';
    $_clientSecret = '';
    $api_cred_file = getenv('HOME') . '/acquia_cloud_api_creds.php';
    if (!file_exists($api_cred_file)) {
      throw new \Exception('Acquia cloud cred file acquia_cloud_api_creds.php missing at home directory.');
    }
    require $api_cred_file;
    $config = [
      'key' => $_clientId,
      'secret' => $_clientSecret,
    ];

    $connector = new Connector($config);
    $client = Client::factory($connector);
    $client->addQuery('filter', "status=$status");
    // @todo Create endpoint for hosting tasks in https://github.com/typhonius/acquia-php-sdk-v2.
    $tasks = $client->request(
      'GET',
      "/applications/$applicationUuid/hosting-tasks",
    );
    $task = array_filter($tasks, function ($value) use ($tag) {
      return strpos($value->description, "refs/tags/$tag");
    });
    echo (integer) !empty($task);
  }

}
