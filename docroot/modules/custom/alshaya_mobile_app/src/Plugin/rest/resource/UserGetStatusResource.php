<?php

namespace Drupal\alshaya_mobile_app\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\user\UserInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
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

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('alshaya_mobile_app')
    );
  }

  /**
   * Receive GET request with email information to send status of the user.
   *
   * @param string $email
   *   Email address for which we need to return status.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The response containing status of the user.
   */
  public function get($email) {
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
