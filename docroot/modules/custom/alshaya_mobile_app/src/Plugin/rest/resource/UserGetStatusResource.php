<?php

namespace Drupal\alshaya_mobile_app\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\user\UserInterface;
use Drupal\rest\ModifiedResourceResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\alshaya_mobile_app\Service\MobileAppUtility;
use Psr\Log\LoggerInterface;

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
   * The mobile app utility service.
   *
   * @var \Drupal\alshaya_mobile_app\Service\MobileAppUtility
   */
  protected $mobileAppUtility;

  /**
   * AlshayaErrorMessages constructor.
   *
   * @param array $configuration
   *   Configuration array.
   * @param string $plugin_id
   *   Plugin id.
   * @param mixed $plugin_definition
   *   Plugin definition.
   * @param array $serializer_formats
   *   Serializer formats.
   * @param \Psr\Log\LoggerInterface $logger
   *   Logger channel.
   * @param \Drupal\alshaya_mobile_app\Service\MobileAppUtility $mobile_app_utility
   *   The mobile app utility service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, array $serializer_formats, LoggerInterface $logger, MobileAppUtility $mobile_app_utility) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->mobileAppUtility = $mobile_app_utility;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('alshaya_mobile_app'),
      $container->get('alshaya_mobile_app.utility')
    );
  }

  /**
   * Receive GET request with email information to send status of the user.
   *
   * @param string $email
   *   Email address for which we need to return status.
   *
   * @return \Drupal\rest\ModifiedResourceResponse
   *   The response containing status of the user.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
   *   Throws when term provided not exists.
   */
  public function get(string $email) {
    $response_data = [
      'status' => FALSE,
    ];

    if (!empty($email)) {
      $user = user_load_by_mail($email);
      if (!$user instanceof UserInterface) {
        $this->mobileAppUtility->throwException('User not found.');
      }
      if ($user->isActive()) {
        $response_data = [
          'status' => TRUE,
          'email' => $email,
        ];
      }
    }

    return (new ModifiedResourceResponse($response_data));
  }

}
