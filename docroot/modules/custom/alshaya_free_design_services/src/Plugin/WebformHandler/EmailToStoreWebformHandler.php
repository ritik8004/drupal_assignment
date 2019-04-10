<?php

namespace Drupal\alshaya_free_design_services\Plugin\WebformHandler;

use Drupal\alshaya_stores_finder_transac\StoresFinderUtility;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\node\NodeInterface;
use Drupal\webform\Plugin\WebformElementManagerInterface;
use Drupal\webform\Plugin\WebformHandler\EmailWebformHandler;
use Drupal\webform\WebformSubmissionConditionsValidatorInterface;
use Drupal\webform\WebformSubmissionInterface;
use Drupal\webform\WebformThemeManagerInterface;
use Drupal\webform\WebformTokenManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Webform submission email to store.
 *
 * @WebformHandler(
 *   id = "email_to_store",
 *   label = @Translation("Email to store"),
 *   category = @Translation("Notification"),
 *   description = @Translation("Sends Mail to Store mail address."),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_UNLIMITED,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 *   submission = \Drupal\webform\Plugin\WebformHandlerInterface::SUBMISSION_OPTIONAL,
 *   tokens = TRUE,
 * )
 */
class EmailToStoreWebformHandler extends EmailWebformHandler {

  /**
   * Stores Finder Utility.
   *
   * @var \Drupal\alshaya_stores_finder_transac\StoresFinderUtility
   */
  protected $storesFinderUtility;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              LoggerChannelFactoryInterface $logger_factory,
                              ConfigFactoryInterface $config_factory,
                              EntityTypeManagerInterface $entity_type_manager,
                              WebformSubmissionConditionsValidatorInterface $conditions_validator,
                              AccountInterface $current_user,
                              ModuleHandlerInterface $module_handler,
                              LanguageManagerInterface $language_manager,
                              MailManagerInterface $mail_manager,
                              WebformThemeManagerInterface $theme_manager,
                              WebformTokenManagerInterface $token_manager,
                              WebformElementManagerInterface $element_manager,
                              StoresFinderUtility $stores_finder_utility) {
    parent::__construct($configuration,
      $plugin_id,
      $plugin_definition,
      $logger_factory,
      $config_factory,
      $entity_type_manager,
      $conditions_validator,
      $current_user,
      $module_handler,
      $language_manager,
      $mail_manager,
      $theme_manager,
      $token_manager,
      $element_manager
    );

    $this->storesFinderUtility = $stores_finder_utility;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('logger.factory'),
      $container->get('config.factory'),
      $container->get('entity_type.manager'),
      $container->get('webform_submission.conditions_validator'),
      $container->get('current_user'),
      $container->get('module_handler'),
      $container->get('language_manager'),
      $container->get('plugin.manager.mail'),
      $container->get('webform.theme_manager'),
      $container->get('webform.token_manager'),
      $container->get('plugin.manager.webform.element'),
      $container->get('alshaya_stores_finder_transac.utility')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $form['to']['to_mail']['to_mail']['#type'] = 'textfield';
    $form['to']['to_mail']['to_mail']['#value'] = '[webform_submission:values:preferred_store:raw]';
    $form['to']['to_mail']['to_mail']['#attributes']['readonly'] = 'readonly';
    return $form;
  }

  /**
   * Get message to, cc, bcc, and from email addresses.
   *
   * @param \Drupal\webform\WebformSubmissionInterface $webform_submission
   *   A webform submission.
   * @param string $configuration_name
   *   The email configuration name. (i.e. to, cc, bcc, or from)
   * @param string $configuration_value
   *   The email configuration value.
   *
   * @return array
   *   An array of email addresses and/or tokens.
   */
  protected function getMessageEmails(WebformSubmissionInterface $webform_submission, $configuration_name, $configuration_value) {
    $emails = parent::getMessageEmails($webform_submission, $configuration_name, $configuration_value);

    if ($configuration_name === 'to') {
      foreach ($emails as $index => $email) {
        $store = $this->storesFinderUtility->getStoreFromCode($email);
        if ($store instanceof NodeInterface) {
          $email = $store->get('field_store_email')->getString();

          // Use site's mail if nothing in store.
          if (empty($email)) {
            $email = $this->configFactory->get('system.site')->get('mail');
          }

          $emails[$index] = $email;
        }
      }
    }

    return $emails;
  }

}
