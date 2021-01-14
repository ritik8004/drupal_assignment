<?php

namespace Drupal\alshaya_purge\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\acquia_purge\HostingInfoInterface;

/**
 * Configure Alshaya Purge settings for IP mapping onto Load Balancer.
 */
class ConfigForm extends ConfigFormBase {
  /**
   * {@inheritdoc}
   */

  /**
   * Constructs a class object.
   *
   * @param \Drupal\acquia_purge\HostingInfoInterface $acquia_purge_hostinginfo
   *   Technical information accessors for the Acquia Cloud environment.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   Messenger service.
   */
  public function __construct(HostingInfoInterface $acquia_purge_hostinginfo, MessengerInterface $messenger) {
    // Take the IP addresses from the 'reverse_proxies' setting.
    $this->messenger = $messenger;
    $this->acquiaPurgeHostingInfo = $acquia_purge_hostinginfo;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('acquia_purge.hostinginfo'),
      $container->get('messenger'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'alshaya_purge_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['alshaya_purge.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $config = $this->config('alshaya_purge.settings');

    $ip_addresses = $this->acquiaPurgeHostingInfo->getBalancerAddresses();
    if (!empty($ip_addresses)) {
      foreach ($ip_addresses as $value) {
        $value = str_replace('.enterprise-g1.hosting.acquia.com', '', gethostbyaddr($value));
        $options[$value] = $value;
      }
    }
    if (!empty($options)) {
      $form['_credentials_fieldset']['storage_method'] = [
        '#type' => 'checkboxes',
        '#title' => $this->t('Select the IP addresses of the Load Balancers to use for this site.'),
        '#options' => $options,
        '#required' => TRUE,
        '#description' => $this->t('This site should be configured to target particular Load Balancers.'),
        '#default_value' => $config->get('ipv4_addresses'),
      ];
    }
    else {
      $this->messenger->addMessage($this->t('No balancer were discovered.'), 'error');
      unset($form['actions']);
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('alshaya_purge.settings');
    $config->set('ipv4_addresses', array_filter($form_state->getValue('storage_method')));
    $config->save();
    return parent::submitForm($form, $form_state);
  }

}
