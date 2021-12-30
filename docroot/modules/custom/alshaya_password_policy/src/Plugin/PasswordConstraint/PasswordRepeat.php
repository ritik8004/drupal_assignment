<?php

namespace Drupal\alshaya_password_policy\Plugin\PasswordConstraint;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\password_policy\PasswordConstraintBase;
use Drupal\password_policy\PasswordPolicyValidation;
use Drupal\Core\Database\Database;
use Drupal\user\UserInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Password\PasswordInterface;

/**
 * Enforces not to use previously used passwords.
 *
 * @PasswordConstraint(
 *   id = "password_policy_repeat_constraint",
 *   title = @Translation("Password Repeat"),
 *   description = @Translation("Provide restrictions on previously used passwords."),
 *   error_message = @Translation("You have used the same password previously and cannot."),
 * )
 */
class PasswordRepeat extends PasswordConstraintBase implements ContainerFactoryPluginInterface {

  /**
   * The password service.
   *
   * @var \Drupal\Core\Password\PasswordInterface
   */
  protected $passwordService;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('password')
    );
  }

  /**
   * Constructs a new PasswordRepeat constraint.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Password\PasswordInterface $password_service
   *   The password service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, PasswordInterface $password_service) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->passwordService = $password_service;
  }

  /**
   * {@inheritdoc}
   */
  public function validate($password, UserInterface $user_context) {
    $configuration = $this->getConfiguration();
    $validation = new PasswordPolicyValidation();
    if (empty($user_context->id())) {
      return $validation;
    }

    // Query for users hashes.
    $hashes = Database::getConnection()->select('password_policy_history', 'pph')
      ->fields('pph', ['pass_hash'])
      ->condition('uid', $user_context->id())
      ->orderBy('timestamp', 'DESC')
      ->range(0, $configuration['history_repeats'])
      ->execute()
      ->fetchAll();

    $repeats = FALSE;
    foreach ($hashes as $hash) {
      if ($this->passwordService->check($password, $hash->pass_hash)) {
        $repeats = TRUE;
        break;
      }
    }

    if ($repeats) {
      $validation->setErrorMessage($this->t('Password has been reused too many times. Choose a different password.'));
    }

    return $validation;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'history_repeats' => 0,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['history_repeats'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Number of old passwords not allowed'),
      '#description' => $this->t('A value of 0 represents no allowed repeats'),
      '#default_value' => $this->getConfiguration()['history_repeats'],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    if (!is_numeric($form_state->getValue('history_repeats')) or $form_state->getValue('history_repeats') < 0) {
      $form_state->setErrorByName('history_repeats', $this->t('The number of previous passwords not allowed must be zero or greater.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['history_repeats'] = $form_state->getValue('history_repeats');
  }

  /**
   * {@inheritdoc}
   */
  public function getSummary() {
    return $this->t('Number of previous passwords not allowed: @number-repeats', ['@number-repeats' => $this->configuration['history_repeats']]);
  }

}
