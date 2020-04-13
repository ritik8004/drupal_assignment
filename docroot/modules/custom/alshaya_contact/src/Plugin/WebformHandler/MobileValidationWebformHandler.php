<?php

namespace Drupal\alshaya_contact\Plugin\WebformHandler;

use Drupal\Core\Form\FormStateInterface;
use Drupal\webform\Plugin\WebformHandlerBase;
use Drupal\webform\WebformSubmissionInterface;
use Drupal\mobile_number\MobileNumberUtilInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\webform\WebformSubmissionConditionsValidatorInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Webform validate handler.
 *
 * @WebformHandler(
 *   id = "webform_mobile_number_validation",
 *   label = @Translation("Mobile Number Validation"),
 *   category = @Translation("Validation"),
 *   description = @Translation("Mobile Number Validation."),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_SINGLE,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 *   submission = \Drupal\webform\Plugin\WebformHandlerInterface::SUBMISSION_OPTIONAL,
 * )
 */
class MobileValidationWebformHandler extends WebformHandlerBase {

  use StringTranslationTrait;

  /**
   * The mobile util service.
   *
   * @var \Drupal\mobile_number\MobileNumberUtilInterface
   */
  protected $mobileUtil;

  /**
   * MobileValidationWebform constructor.
   *
   * @param array $configuration
   *   Configuration array.
   * @param string $plugin_id
   *   Plugin id.
   * @param mixed $plugin_definition
   *   Plugin definition.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   Logger Factory.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Configuration Factory.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type Manager.
   * @param \Drupal\webform\WebformSubmissionConditionsValidatorInterface $conditions_validator
   *   Webform condition validate interface.
   * @param \Drupal\mobile_number\MobileNumberUtilInterface $mobile_util
   *   The mobile_util service.
   */

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    LoggerChannelFactoryInterface $logger_factory,
    ConfigFactoryInterface $config_factory,
    EntityTypeManagerInterface $entity_type_manager,
    WebformSubmissionConditionsValidatorInterface $conditions_validator,
    MobileNumberUtilInterface $mobile_util
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $logger_factory, $config_factory, $entity_type_manager, $conditions_validator);
    $this->mobileUtil = $mobile_util;
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
      $container->get('mobile_number.util')
    );
  }

  /**
   * Check maxlenght on mobile number.
   */
  public function validateForm(array &$form, FormStateInterface $form_state, WebformSubmissionInterface $webform_submission) {
    $preference_channel = $webform_submission->getElementData('select_your_preference_of_channel_of_communication');
    $mobile_number = $webform_submission->getElementData('dummy_field_mobile_number');
    if ($preference_channel == 'Mobile' && empty($mobile_number)) {
      $form_state->setErrorByName('mobile_number', $this->t('Mobile Number is mandatory'));
    }
    if (!empty($mobile_number)) {
      $mobile_number_obj = $this->mobileUtil->getMobileNumber($mobile_number);
      if (!is_object($mobile_number_obj)) {
        $form_state->setErrorByName('mobile_number', $this->t('Invalid mobile number'));
      }
    }
  }

}
