<?php

namespace Drupal\alshaya_mobile_app\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Psr\Log\LoggerInterface;
use Drupal\user\UserInterface;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\alshaya_mobile_app\Service\MobileAppUtility;

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

  use StringTranslationTrait;

  /**
   * The mail manager.
   *
   * @var \Drupal\Core\Mail\MailManagerInterface
   */
  protected $mailManager;

  /**
   * The mobile app utility service.
   *
   * @var \Drupal\alshaya_mobile_app\Service\MobileAppUtility
   */
  protected $mobileAppUtility;

  /**
   * UserRegistrationMail constructor.
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
   * @param \Drupal\Core\Mail\MailManagerInterface $mail_manager
   *   The mail manager.
   * @param \Drupal\alshaya_mobile_app\Service\MobileAppUtility $mobile_app_utility
   *   The mobile app utility service.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    array $serializer_formats,
    LoggerInterface $logger,
    MailManagerInterface $mail_manager,
    MobileAppUtility $mobile_app_utility
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->mailManager = $mail_manager;
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
      $container->get('plugin.manager.mail'),
      $container->get('alshaya_mobile_app.utility')
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
      $this->logger->error('Invalid data to send an email to user.');
      return $this->mobileAppUtility->sendStatusResponse($this->t('Invalid data to send an email to user.'));
    }

    /* @var \Drupal\user\Entity\User $user */
    $user = user_load_by_mail($email);
    if ($user instanceof UserInterface) {
      $this->logger->error('User with email @email already exist.', ['@email' => $email]);
      return $this->mobileAppUtility->sendStatusResponse(
        $this->t('Can not send an email, user with email @email already exist.', ['@email' => $email])
      );
    }

    // Get user from mdc and create new user account, when user does not
    // exists in drupal.
    $user = $this->mobileAppUtility->createUserFromCommerce($email);

    if (!$user instanceof UserInterface) {
      $this->logger->error('User with email @email does not exist.', ['@email' => $email]);
      return $this->mobileAppUtility->sendStatusResponse(
        $this->t('User with email @email does not exist.', ['@email' => $email])
      );
    }

    // Mail one time login URL and instructions using current language.
    $params['account'] = $user;
    $mail = $this->mailManager->mail(
      'user_registrationpassword',
      'register_confirmation_with_pass',
      $user->getEmail(),
      $user->getPreferredLangcode(),
      $params
    );

    if (!$mail['result']) {
      $this->logger->warning('Can not able to send an email to @email.', ['@mail' => $email]);
      return $this->mobileAppUtility->sendStatusResponse(
        $this->t('Unable to send email. Contact the site administrator if the problem persists')
      );
    }

    return $this->mobileAppUtility->sendStatusResponse('', TRUE);
  }

}
