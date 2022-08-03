<?php

namespace Alshaya\Blt\Plugin\Commands;

use Acquia\Blt\Robo\BltTasks;
use AcquiaCloudApi\Connector\Client;
use AcquiaCloudApi\Connector\Connector;

/**
 * This class defines wrapper around cloud tasks.
 *
 * @todo cleanup and move this to public shared repo.
 */
class CloudTasksCommand extends BltTasks {

  /**
   * Check if cloud task is still in progress.
   *
   * @param string $identifier
   *   Tag or branch we need to check for.
   * @param string $applicationUuid
   *   Acquia Cloud application UUID.
   *
   * @command acquia-cloud:task-available
   * @aliases acquia-cloud-task-available
   *
   * @description Check if cloud task for tag or branch is still in progress.
   *
   * @throws \Exception
   */
  public function isCloudTaskAvailable(string $identifier, string $applicationUuid = '') {
    $api_cred_file = getenv('HOME') . '/acquia_cloud_api_creds.php';
    if (!file_exists($api_cred_file)) {
      throw new \Exception('Acquia cloud cred file acquia_cloud_api_creds.php missing at home directory.');
    }

    // Try to set application UUID from ENV if not passed.
    if (empty($applicationUuid)) {
      $applicationUuid = getenv('AH_APPLICATION_UUID');

      // If still empty, we can't proceed further.
      if (empty($applicationUuid)) {
        throw new \Exception('APPLICATION UUID not passed and not available in ENV as well.');
      }
    }

    // Two possible values for this - "in-progress", "completed".
    $status = 'in-progress';

    $_clientId = '';
    $_clientSecret = '';

    // Above variables should be defined in the file.
    require $api_cred_file;

    $config = [
      'key' => $_clientId,
      'secret' => $_clientSecret,
    ];

    $connector = new Connector($config);

    // @todo Replace this with new API once available.
    // This is deprecated but still used in Acquia Cloud UI.
    $client = Client::factory($connector);
    $client->addQuery('filter', "status=$status");
    try {
      $tasks = $client->request(
        'GET',
        "/applications/$applicationUuid/hosting-tasks",
      );
    }
    catch (\Exception $e) {
      if ($e->getCode() == 404 || $e->getCode() === 'not_found') {
        // Cloud tasks API gave 404, let's just sleep for 2 min.
        echo 404;
        return;
      }

      throw $e;
    }

    // Filter out the tasks with the one we are interested in.
    $tasks = array_filter($tasks, fn($value) => strpos($value->description, $identifier) > -1);

    // Say 1 if we found the task.
    echo empty($tasks) ? 0 : 1;
  }

}
