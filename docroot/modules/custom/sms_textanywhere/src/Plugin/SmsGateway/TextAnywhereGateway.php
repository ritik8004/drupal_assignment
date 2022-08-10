<?php

namespace Drupal\sms_textanywhere\Plugin\SmsGateway;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\sms\Message\SmsDeliveryReport;
use Drupal\sms\Message\SmsMessageReportStatus;
use Drupal\sms\Message\SmsMessageResultStatus;
use Drupal\sms\Plugin\SmsGatewayPluginBase;
use Drupal\sms\Message\SmsMessageInterface;
use Drupal\sms\Message\SmsMessageResult;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a TextAnywhere gateway to send SMSes using SOAP based service API.
 *
 * @SmsGateway(
 *   id = "textanywhere",
 *   label = @Translation("TextAnywhere"),
 *   outgoing_message_max_recipients = -1,
 * )
 */
class TextAnywhereGateway extends SmsGatewayPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The configFactory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * TextAnywhereGateway constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The Config factory object.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactoryInterface $config_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $defaults = [];
    $defaults['textanywhere'] = [
      'service_url'  => 'http://www.textapp.net/webservice/service.asmx?wsdl',
      'client_billing_reference'  => '',
      'client_message_reference'  => '',
      'originator'  => '',
      'validity'  => 72,
      'character_set_id'  => 2,
      'reply_method_id'  => 1,
      'reply_data'  => '',
      'status_notification_url'  => '',
    ];
    return $defaults;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $config = $this->getConfiguration();

    $form['textanywhere'] = [
      '#type' => 'details',
      '#title' => $this->t('TextAnywhere Gateway API information'),
      '#tree' => TRUE,
      '#open' => TRUE,
    ];
    $form['textanywhere']['service_url'] = [
      '#type' => 'url',
      '#title' => $this->t('Service URL'),
      '#required' => TRUE,
      '#default_value' => $config['textanywhere']['service_url'],
      '#description' => $this->t('A SOAP based service url to integrate with TextAnywhere API.'),
    ];
    $form['textanywhere']['client_billing_reference'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Billing refrence'),
      '#required' => TRUE,
      '#default_value' => $config['textanywhere']['client_billing_reference'],
      '#description' => $this->t('A value you give to help aggregate messages together for onward billing purposes.'),
    ];
    $form['textanywhere']['client_message_reference'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Message refrence'),
      '#required' => TRUE,
      '#default_value' => $config['textanywhere']['client_message_reference'],
      '#description' => $this->t('Sets the Client Reference of your message.'),
    ];
    $form['textanywhere']['originator'] = [
      '#type' => 'number',
      '#title' => $this->t('Originator'),
      '#default_value' => $config['textanywhere']['originator'],
      '#required' => TRUE,
      '#maxlength' => 11,
      '#description' => $this->t('An 11-character string or phone number that shows the recipient who sent the SMS.'),
    ];
    $form['textanywhere']['validity'] = [
      '#type' => 'number',
      '#title' => $this->t('Validity'),
      '#required' => TRUE,
      '#default_value' => $config['textanywhere']['validity'],
      '#maxlength' => 2,
      '#description' => $this->t('The length of time in hours that a message will be attempted for delivery before expiring.'),
    ];
    $form['textanywhere']['character_set_id'] = [
      '#type' => 'number',
      '#title' => $this->t('Character Set Id'),
      '#required' => TRUE,
      '#default_value' => $config['textanywhere']['character_set_id'],
      '#maxlength' => 1,
      '#description' => $this->t('The Character Set of the message body, most commonly set to 2 (GSM 03.38), or 1 (Unicode).'),
    ];
    $form['textanywhere']['reply_method_id'] = [
      '#type' => 'number',
      '#title' => $this->t('Reply Method ID'),
      '#required' => TRUE,
      '#default_value' => $config['textanywhere']['reply_method_id'],
      '#maxlength' => 1,
      '#description' => $this->t('The message will be sent with your originator value.'),
    ];
    $form['textanywhere']['reply_data'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Reply Data'),
      '#default_value' => $config['textanywhere']['reply_data'],
      '#description' => $this->t('Provides the location that any replies should be sent to.'),
    ];
    $form['textanywhere']['status_notification_url'] = [
      '#type' => 'url',
      '#title' => $this->t('Status Notification Url'),
      '#default_value' => $config['textanywhere']['status_notification_url'],
      '#description' => $this->t('Your website address where, if requested, message delivery statuses will be posted.'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['textanywhere']['service_url'] = trim($form_state->getValue([
      'textanywhere',
      'service_url',
    ]));
    $this->configuration['textanywhere']['client_billing_reference'] = trim($form_state->getValue([
      'textanywhere',
      'client_billing_reference',
    ]));
    $this->configuration['textanywhere']['client_message_reference'] = trim($form_state->getValue([
      'textanywhere',
      'client_message_reference',
    ]));
    $this->configuration['textanywhere']['originator'] = trim($form_state->getValue([
      'textanywhere',
      'originator',
    ]));
    $this->configuration['textanywhere']['validity'] = trim($form_state->getValue([
      'textanywhere',
      'validity',
    ]));
    $this->configuration['textanywhere']['character_set_id'] = trim($form_state->getValue([
      'textanywhere',
      'character_set_id',
    ]));
    $this->configuration['textanywhere']['reply_method_id'] = trim($form_state->getValue([
      'textanywhere',
      'reply_method_id',
    ]));
    $this->configuration['textanywhere']['reply_data'] = trim($form_state->getValue([
      'textanywhere',
      'reply_data',
    ]));
    $this->configuration['textanywhere']['status_notification_url'] = trim($form_state->getValue([
      'textanywhere',
      'status_notification_url',
    ]));
  }

  /**
   * {@inheritdoc}
   */
  public function send(SmsMessageInterface $sms) {
    $login = $this->configFactory->get('alshaya_kz_transac_lite.settings')->get('sms_textanywhere_external_login');
    $pass = $this->configFactory->get('alshaya_kz_transac_lite.settings')->get('sms_textanywhere_external_pass');
    $client = new \SoapClient($this->configuration['textanywhere']['service_url']);
    $result = new SmsMessageResult();

    $params = new \stdClass();
    $params->returnCSVString = FALSE;
    $params->externalLogin = $login;
    $params->password = $pass;
    $params->clientBillingReference = $this->configuration['textanywhere']['client_billing_reference'];
    $params->clientMessageReference = $this->configuration['textanywhere']['client_message_reference'];
    $params->originator = $this->configuration['textanywhere']['originator'];
    $params->destinations = implode(';', $sms->getRecipients());
    $params->body = utf8_encode($sms->getMessage());
    $params->validity = $this->configuration['textanywhere']['validity'];
    $params->characterSetID = $this->configuration['textanywhere']['character_set_id'];
    $params->replyMethodID = $this->configuration['textanywhere']['reply_method_id'];
    $params->replyData = $this->configuration['textanywhere']['reply_data'];
    $params->statusNotificationUrl = $this->configuration['textanywhere']['status_notification_url'];

    try {
      $response = $client->__soapCall('SendSMS', [$params]);
      if (str_contains($response->SendSMSResult, 'Transaction OK')) {
        foreach ($sms->getRecipients() as $recipient) {
          $report = (new SmsDeliveryReport())
            ->setRecipient($recipient)
            ->setStatus(SmsMessageReportStatus::QUEUED);

          $result->addReport($report);
        }
      }
      else {
        foreach ($sms->getRecipients() as $recipient) {
          $report = (new SmsDeliveryReport())
            ->setRecipient($recipient)
            ->setStatus(SmsMessageReportStatus::ERROR);

          $result->addReport($report)
            ->setError(1)
            ->setErrorMessage($response->SendSMSResult);
        }
      }

      return $result;

    }
    catch (\Exception $e) {
      return $result
        ->setError(SmsMessageResultStatus::ERROR)
        ->setErrorMessage($e->getMessage());
    }
  }

}
