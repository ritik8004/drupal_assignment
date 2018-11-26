<?php

namespace Drupal\alshaya_mobile_app\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Psr\Log\LoggerInterface;
use Drupal\acq_commerce\Conductor\APIWrapper;
use Drupal\user\UserInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Class UserRegistrationMail.
 *
 * @RestResource(
 *   id = "user_registration_mail",
 *   label = @Translation("Alshaya user registration mail"),
 *   uri_paths = {
 *     "canonical" = "/rest/v1/user/send-registration-email",
 *     "https://www.drupal.org/link-relations/create" = "/rest/v1/user/send-registration-email"
 *   }
 * )
 */
class UserRegistrationMail extends ResourceBase {

  /**
   * API Wrapper object.
   *
   * @var \Drupal\acq_commerce\Conductor\APIWrapper
   */
  protected $apiWrapper;

  /**
   * API Wrapper object.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * SimplePageResource constructor.
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
   * @param \Drupal\acq_commerce\Conductor\APIWrapper $api_wrapper
   *   The renderer.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The renderer.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    array $serializer_formats,
    LoggerInterface $logger,
    APIWrapper $api_wrapper,
    ModuleHandlerInterface $module_handler
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->apiWrapper = $api_wrapper;
    $this->moduleHandler = $module_handler;
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
      $container->get('acq_commerce.api'),
      $container->get('module_handler')
    );
  }

  /**
   * Receive POST request with email information to send an email.
   *
   * @param array $data
   *   Post data with required email key.
   *
   * @return \Drupal\rest\ResourceResponse
   *   HTTP Response.
   */
  public function post(array $data) {
    $email = $data['email'] ?? FALSE;

    if (empty($email)) {
      $this->logger->error('Invalid data to send an email to customer.');
      return $this->sendStatusResponse();
    }

    /* @var \Drupal\user\Entity\User $user */
    $user = user_load_by_mail($email);
    // If there is user with given email.
    if (!$user instanceof UserInterface) {
      // Try API now.
      try {
        /** @var \Drupal\acq_commerce\Conductor\APIWrapper $api_wrapper */
        $customer = $this->apiWrapper->getCustomer($email);

        if (!empty($customer)) {
          $this->moduleHandler->loadInclude('alshaya_acm_customer', 'inc', 'alshaya_acm_customer.utility');
          /** @var \Drupal\user\Entity\User $user */
          $user = alshaya_acm_customer_create_drupal_user($customer);
        }
      }
      catch (\Exception $e) {
        // Do nothing except for downtime exception, let default validation
        // handle the error messages.
        if (acq_commerce_is_exception_api_down_exception($e)) {
          $this->logger->error($e->getMessage());
        }
      }
    }

    if (!$user instanceof UserInterface) {
      $this->logger->warning('User with email %email doesn\'t exist.', ['%email' => $email]);
      return $this->sendStatusResponse();
    }

    // Mail one time login URL and instructions using current language.
    $params['account'] = $user;
    $mail = \Drupal::service('plugin.manager.mail')
      ->mail(
        'user_registrationpassword',
        'register_confirmation_with_pass',
        $user->getEmail(),
        $user->getPreferredLangcode(),
        $params
      );

    if (!$mail['result']) {
      return $this->sendStatusResponse();
    }

    return $this->sendStatusResponse(TRUE);
  }

  /**
   * Helper method to return a response.
   *
   * @param bool $status
   *   (optional) True if you want to send success => TRUE, else FALSE.
   *
   * @return \Drupal\rest\ResourceResponse
   *   HTTP Response.
   */
  protected function sendStatusResponse($status = FALSE) {
    $response['success'] = (bool) ($status);
    return (new ResourceResponse($response));
  }

}
