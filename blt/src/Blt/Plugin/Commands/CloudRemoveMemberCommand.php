<?php

namespace Alshaya\Blt\Plugin\Commands;

use Acquia\Blt\Robo\BltTasks;
use AcquiaCloudApi\Connector\Client;
use AcquiaCloudApi\Connector\Connector;
use AcquiaCloudApi\Endpoints\Organizations;

/**
 * This class defines wrapper around cloud member.
 */
class CloudRemoveMemberCommand extends BltTasks {

  /**
   * Remove member from ACE.
   *
   * @param string $user_emails
   *   User emails to process.
   *
   * @command acquia-cloud:remove-member
   * @aliases acquia-cloud-remove-member
   *
   * @description Remove the list of members from Acquia cloud.
   *
   * @throws \Exception
   */
  public function removeMember(string $user_emails) {
    $api_cred_file = getenv('HOME') . '/acquia_cloud_api_creds.php';
    if (!file_exists($api_cred_file)) {
      throw new \Exception('Acquia cloud cred file acquia_cloud_api_creds.php missing at home directory.');
    }

    $_clientId = '';
    $_clientSecret = '';

    // Above variables should be defined in the file.
    require $api_cred_file;

    $config = [
      'key' => $_clientId,
      'secret' => $_clientSecret,
    ];

    // Get members with organization uuid.
    $connector = new Connector($config);
    $client = Client::factory($connector);
    $client->addQuery('limit', 500);
    $organization = new Organizations($client);
    $organization_uuid = $this->getConfigValue('cloud.organization_uuid');
    $response = $organization->getMembers($organization_uuid);
    $removed_members = [];
    $user_emails = explode(',', $user_emails);
    foreach ($response->getArrayCopy() as $member) {
      // Check if found the user in members list then delete the member.
      if (in_array($member->mail, $user_emails)) {
        $organization->deleteMember($organization_uuid, $member->uuid);
        $removed_members[$member->mail] = 'Organization member removed.';
        echo "User deleted from Acquia cloud with Email: $member->mail \n";
      }
    }

    // Check if user doesn't exist in acquia cloud and echo the list.
    if (count($removed_members) != count($user_emails)) {
      foreach (array_diff($user_emails, $removed_members) as $mail) {
        echo "User not found in Acquia cloud with Email: $mail \n";
      }
    }
  }

}
