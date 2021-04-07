<?php

namespace Drupal\alshaya_mobile_app\Plugin\rest\resource;

use Drupal\alshaya_mobile_app\Service\MobileAppUtility;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Component\Utility\Crypt;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Drupal\user\UserInterface;
use Drupal\alshaya_api\AlshayaApiWrapper;
use http\Exception\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class User Reset Password.
 *
 * @RestResource(
 *   id = "user_reset_password",
 *   label = @Translation("Alshaya user reset password"),
 *   uri_paths = {
 *     "canonical" = "/rest/v1/user/reset-password",
 *     "https://www.drupal.org/link-relations/create" = "/rest/v1/user/reset-password"
 *   }
 * )
 */
class UserResetPassword extends ResourceBase {

  /**
   * The mobile app utility service.
   *
   * @var \Drupal\alshaya_mobile_app\Service\MobileAppUtility
   */
  protected $mobileAppUtility;

  /**
   * Entity Type Manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * DateTime service.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $time;

  /**
   * Plugin manager of the password constraints.
   *
   * @var \Drupal\Component\Plugin\PluginManagerInterface
   */
  protected $passwordPolicyManager;

  /**
   * Api wrapper.
   *
   * @var \Drupal\alshaya_api\AlshayaApiWrapper
   */
  protected $apiWrapper;

  /**
   * UserResetPassword constructor.
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
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity Type Manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   DateTime service.
   * @param \Drupal\Component\Plugin\PluginManagerInterface $password_policy_manager
   *   The plugin manager for the password constraints.
   * @param \Drupal\alshaya_api\AlshayaApiWrapper $api_wrapper
   *   Api wrapper.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              array $serializer_formats,
                              LoggerInterface $logger,
                              MobileAppUtility $mobile_app_utility,
                              EntityTypeManagerInterface $entity_type_manager,
                              ConfigFactoryInterface $config_factory,
                              TimeInterface $time,
                              PluginManagerInterface $password_policy_manager,
                              AlshayaApiWrapper $api_wrapper) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->mobileAppUtility = $mobile_app_utility;
    $this->entityTypeManager = $entity_type_manager;
    $this->configFactory = $config_factory;
    $this->time = $time;
    $this->passwordPolicyManager = $password_policy_manager;
    $this->apiWrapper = $api_wrapper;
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
      $container->get('entity_type.manager'),
      $container->get('config.factory'),
      $container->get('datetime.time'),
      $container->get('plugin.manager.password_policy.password_constraint'),
      $container->get('alshaya_api.api')
    );
  }

  /**
   * Process the data in POST and reset user password.
   *
   * @param array $data
   *   POST data.
   *
   * @return \Drupal\rest\ResourceResponse
   *   Response.
   */
  public function post(array $data) {
    $new_password = trim($data['new_password'] ?? '');
    if (empty($data['reset_token'])
      || empty($data['timestamp'])
      || empty($data['user_id'])
      || empty($new_password)) {
      throw new InvalidArgumentException();
    }

    $user = $this->entityTypeManager->getStorage('user')->load($data['user_id']);
    if (!($user instanceof UserInterface)) {
      return $this->mobileAppUtility->sendStatusResponse($this->t('Invalid user id.'));
    }

    $hash = $data['reset_token'];
    $timestamp = $data['timestamp'];
    $current = $this->time->getRequestTime();

    // Time out, in seconds, until login URL expires.
    $timeout = $this->configFactory->get('user.settings')->get('password_reset_timeout');

    if ($current - $timestamp > $timeout) {
      return $this->mobileAppUtility->sendStatusResponse($this->t('You have tried to use a one-time login link that has expired. Please request a new one.'));
    }

    // If last login time is greater than timestamp. Expire one time login link.
    if ($user->getLastLoginTime() >= $timestamp) {
      return $this->mobileAppUtility->sendStatusResponse($this->t('You have tried to use a one-time login link that has either been used or is no longer valid. Please request a new one.'));
    }

    if (!(Crypt::hashEquals($hash, user_pass_rehash($user, $timestamp)))) {
      return $this->mobileAppUtility->sendStatusResponse($this->t('You have tried to use a one-time login link that has either been used or is no longer valid. Please request a new.'));
    }

    if ($errors = $this->validateNewPassword($user, $new_password)) {
      $return = [
        'success' => FALSE,
        'errors' => $errors,
        'message' => $this->t('The password does not satisfy the password policies.'),
      ];
      return (new ResourceResponse($return));
    }

    try {
      // We only update password for non-admin and actual
      // customers on the mdc.
      if (alshaya_acm_customer_is_customer($user)) {
        $customer_id = $user->get('acq_customer_id')->getString();
        $this->apiWrapper->updateCustomerPass(['customer_id' => $customer_id], $new_password);
        _alshaya_user_password_policy_history_insert_password_hash($user, $new_password);

        // Update last login time.
        $user->setLastLoginTime($current);
        $user->save();
      }
    }
    catch (\Exception $e) {
      return $this->mobileAppUtility->sendStatusResponse($e->getMessage());
    }

    return $this->mobileAppUtility->sendStatusResponse($this->t('Your password has been changed.'), TRUE);
  }

  /**
   * Validate new password.
   *
   * @param \Drupal\user\UserInterface $user
   *   User.
   * @param string $new_password
   *   New password.
   *
   * @return array
   *   Errors if any.
   */
  protected function validateNewPassword(UserInterface $user, string $new_password) {
    $errors = [];

    $user_context_values = [];
    $user_context_values['mail'] = $user->getEmail();
    $user_context_values['name'] = $user->getAccountName();
    $user_context_values['uid'] = $user->id();

    $policies = $this->getApplicablePolicies($user->getRoles());

    /** @var \Drupal\password_policy\Entity\PasswordPolicy $policy */
    foreach ($policies as $policy) {
      $policy_constraints = $policy->getConstraints();

      foreach ($policy_constraints as $constraint) {
        $plugin = $this->passwordPolicyManager->createInstance($constraint['id'], $constraint);

        // Execute validation.
        $validation = $plugin->validate($new_password, $user_context_values);
        if (!$validation->isValid()) {
          $errors[] = (string) $validation->getErrorMessage();
        }
      }
    }

    return $errors;
  }

  /**
   * Get policies applicable for user's roles.
   *
   * @param array $roles
   *   User's roles.
   *
   * @return array
   *   Applicable policies for user's roles.
   */
  protected function getApplicablePolicies(array $roles) {
    $applicable_policies = [];
    $password_policy_ids = [];
    foreach ($roles as $role_key) {
      $role_map = ['roles.' . $role_key => $role_key];
      $role_policies = $this->getPasswordPolicyStorage()->loadByProperties($role_map);

      /** @var \Drupal\password_policy\Entity\PasswordPolicy $policy */
      foreach ($role_policies as $policy) {
        if (!in_array($policy->id(), $password_policy_ids)) {
          $applicable_policies[$policy->id()] = $policy;
          $password_policy_ids[] = $policy->id();
        }
      }
    }

    return $applicable_policies;
  }

  /**
   * Get password policy storage service.
   *
   * @return \Drupal\Core\Entity\EntityStorageInterface
   *   Password policy storage service.
   */
  protected function getPasswordPolicyStorage() {
    static $storage = NULL;

    if (!isset($storage)) {
      $storage = $this->entityTypeManager->getStorage('password_policy');
    }

    return $storage;
  }

}
