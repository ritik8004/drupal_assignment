<?php

namespace Alshaya\Blt\Plugin\Commands;

use Acquia\Blt\Robo\BltTasks;
use Consolidation\AnnotatedCommand\CommandData;
use League\OAuth2\Client\Provider\GenericProvider;
use GuzzleHttp\Client;
use Robo\Contract\VerbosityThresholdInterface;

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
   * @param string $application
   *   Acquia Cloud application UUID.
   * @param string $tag
   *   Tag we need to search for.
   * @param string $status
   *   Status of deployment task.
   * @param bool $reset_token
   *   Reset access token.
   *
   * @command cloud:get-application-task
   *
   * @description Get cloud deployment task details for application with tag.
   *
   * @throws \Exception
   */
  public function getCloudTask($application, $tag, $status = 'in_progress', $reset_token = FALSE) {
    $access_token_file = $this->getConfigValue('repo.root') . '/cloud_task_access_token';
    $access_token = @file_get_contents($access_token_file);
    try {
      $provider = $this->getCloudGenericProvider();
      // Get access token for cloud task.
      if ($reset_token || empty($access_token)) {
        // Try to get an access token using the client credentials grant.
        $access_token = $this->generateCloudAccessToken($provider, $reset_token);
      }
      // Generate a request object using the access token.
      $request = $provider->getAuthenticatedRequest(
        'GET',
        "https://cloud.acquia.com/api/applications/$application/hosting-tasks?filter=status%3D$status",
        $access_token,
      );

      // Send the request.
      $client = new Client();
      $response = $client->send($request);
      $responseBody = json_decode($response->getBody()->getContents(), TRUE);
      $res = $responseBody['_embedded']['items'];
      $task = array_filter($res, function ($value) use ($tag) {
        return strpos($value['description'], "refs/tags/$tag");
      });
      $task = reset($task);
    }
    catch (\Exception $e) {
      if ($e->getCode() === 403) {
        $this->getCloudTask($application, $tag, $status, TRUE);
      }
      else {
        throw new \Exception('Error occurred while fetching cloud tasks, error:' . $e->getMessage());
      }
    }
    echo (integer) !empty($task);
  }

  /**
   * Writes a access token to ${repo.root}/cloud_access_token.
   *
   * @throws \Exception
   */
  protected function generateCloudAccessToken($provider = NULL, $replace = FALSE) {
    $access_token_file = $this->getConfigValue('repo.root') . '/cloud_task_access_token';
    $this->say("Generating access token for acquia cloud task...");
    try {
      if (!$provider) {
        $provider = $this->getCloudGenericProvider();
      }
      // Try to get an access token using the client credentials grant.
      $access_token = $provider->getAccessToken('client_credentials');
      if ($replace) {
        unlink($access_token_file);
      }
      $result = $this->taskWriteToFile($access_token_file)
        ->text($access_token)
        ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
        ->run();
      if (!$result->wasSuccessful()) {
        $filepath = $this->getInspector()->getFs()->makePathRelative($access_token_file, $this->getConfigValue('repo.root'));
        throw new \Exception("Unable to write access token to $filepath.");
      }
    }
    catch (\Exception $e) {
      throw new \Exception('Error occurred generating access token, error:' . $e->getMessage());
    }
    return $access_token;
  }

  /**
   * Get generic provider for acquia cloud.
   */
  private function getCloudGenericProvider() {
    global $_clientId, $_clientSecret;
    return new GenericProvider([
      'clientId' => $_clientId,
      'clientSecret' => $_clientSecret,
      'urlAuthorize' => '',
      'urlAccessToken' => 'https://accounts.acquia.com/api/auth/oauth/token',
      'urlResourceOwnerDetails' => '',
    ]);
  }

}
