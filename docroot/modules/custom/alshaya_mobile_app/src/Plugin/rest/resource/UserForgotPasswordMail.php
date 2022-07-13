<?php

namespace Drupal\alshaya_mobile_app\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Psr\Log\LoggerInterface;
use Drupal\user\UserInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\alshaya_mobile_app\Service\MobileAppUtility;
use Drupal\Core\Language\LanguageManagerInterface;

/**
 * Class User Forgot Password Mail.
 *
 * @RestResource(
 *   id = "user_forgot_password_mail",
 *   label = @Translation("Alshaya user forgot password mail"),
 *   uri_paths = {
 *     "create" = "/rest/v1/user/forgot-password-email"
 *   }
 * )
 */
class UserForgotPasswordMail extends ResourceBase {

  use StringTranslationTrait;

  /**
   * The mobile app utility service.
   *
   * @var \Drupal\alshaya_mobile_app\Service\MobileAppUtility
   */
  protected $mobileAppUtility;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * UserForgotPasswordMail constructor.
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
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    array $serializer_formats,
    LoggerInterface $logger,
    MobileAppUtility $mobile_app_utility,
    LanguageManagerInterface $language_manager
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->mobileAppUtility = $mobile_app_utility;
    $this->languageManager = $language_manager;
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
      $container->get('alshaya_mobile_app.utility'),
      $container->get('language_manager')
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

    $user = user_load_by_mail($email);
    // Get user from mdc and create new user account, when user does not
    // exists in drupal.
    if (!$user instanceof UserInterface) {
      $user = $this->mobileAppUtility->createUserFromCommerce($email, FALSE);
    }

    if (!$user instanceof UserInterface) {
      $this->logger->error('User with email @email does not exist.', ['@email' => $email]);
      return $this->mobileAppUtility->sendStatusResponse(
        $this->t('User with email @email does not exist.', ['@email' => $email])
      );
    }

    // Get the languacode for the current request.
    $langcode = $this->languageManager->getCurrentLanguage()->getId();

    // Send the password reset email.
    $mail = _user_mail_notify('password_reset', $user, $langcode);
    if (empty($mail)) {
      return $this->mobileAppUtility->sendStatusResponse(
        $this->t('Unable to send email. Contact the site administrator if the problem persists')
      );
    }

    $this->logger->notice(
      'Password reset instructions mailed to @name at @email.',
      ['@name' => $user->getAccountName(), '@email' => $user->getEmail()]
    );
    return $this->mobileAppUtility->sendStatusResponse('', TRUE);
  }

}
