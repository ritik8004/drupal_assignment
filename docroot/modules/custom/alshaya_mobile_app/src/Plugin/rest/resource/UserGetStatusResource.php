<?php

namespace Drupal\alshaya_mobile_app\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\user\UserInterface;
use Drupal\rest\ModifiedResourceResponse;

/**
 * Class UserGetStatusResource.
 *
 * @RestResource(
 *   id = "user_get_status",
 *   label = @Translation("Alshaya get user status"),
 *   uri_paths = {
 *     "canonical" = "/rest/v1/user/get-status/{email}",
 *   }
 * )
 */
class UserGetStatusResource extends ResourceBase {

  /**
   * Receive GET request with email information to send status of the user.
   *
   * @param string $email
   *   Email address for which we need to return status.
   *
   * @return \Drupal\rest\ModifiedResourceResponse
   *   The response containing status of the user.
   */
  public function get(string $email) {
    $response_data = [
      'status' => FALSE,
    ];

    if (!empty($email)) {
      $user = user_load_by_mail($email);
      if ($user instanceof UserInterface && $user->isActive()) {
        $response_data = [
          'status' => TRUE,
          'email' => $email,
        ];
      }
    }

    return (new ModifiedResourceResponse($response_data));
  }

}
