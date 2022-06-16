<?php

namespace Drupal\alshaya_furnitures_free_design_services\Plugin\WebformHandler;

use Drupal\Core\Form\FormStateInterface;
use Drupal\node\NodeInterface;
use Drupal\webform\Plugin\WebformHandler\EmailWebformHandler;
use Drupal\webform\WebformSubmissionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Webform submission email to store.
 *
 * @WebformHandler(
 *   id = "email_to_store",
 *   label = "Email to store",
 *   category = "Notification",
 *   description = "Sends Mail to Store mail address.",
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
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->storesFinderUtility = $container->get('alshaya_stores_finder_transac.utility');
    return $instance;
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
    $return = parent::getMessageEmails($webform_submission, $configuration_name, $configuration_value);

    if ($configuration_name === 'to') {
      foreach ($return as $index => $data) {
        $store = $this->storesFinderUtility->getStoreFromCode($data);
        if ($store instanceof NodeInterface) {
          $email = $store->get('field_store_email')->getString();

          // Use site's mail if nothing in store.
          if (empty($email)) {
            $email = $this->configFactory->get('system.site')->get('mail');
          }

          $return[$index] = $email;
        }
      }
    }

    return $return;
  }

}
