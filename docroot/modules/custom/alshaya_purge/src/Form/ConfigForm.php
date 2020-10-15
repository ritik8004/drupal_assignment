<?php

namespace Drupal\alshaya_purge\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Site\Settings;

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
   * @param \Drupal\Core\Site\Settings $settings
   *   Drupal site settings object.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   Messenger service.
   */
  public function __construct(Settings $settings, MessengerInterface $messenger) {
    // Take the IP addresses from the 'reverse_proxies' setting.
    $reverse_proxies = $settings->get('reverse_proxy_addresses');
    $this->balancerAddresses = [];
    if (!empty($reverse_proxies) && is_array($reverse_proxies)) {
      foreach ($reverse_proxies as $reverse_proxy) {
        if ($reverse_proxy && strpos($reverse_proxy, '.')) {
          $this->balancerAddresses[] = $reverse_proxy;
        }
      }
    }
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('settings'),
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

    $options = $this->balancerAddresses;
    if (!empty($options)) {
      $form['_credentials_fieldset']['storage_method'] = [
        '#type' => 'radios',
        '#title' => $this->t('Select the IP address of the load balancer to use for this site.'),
        '#options' => $options,
        '#required' => TRUE,
        '#description' => $this->t('This site should be configured to target a particular Load Balancer.'),
        '#default_value' => $config->get('ipv4'),
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
    $config->set('ipv4', $form_state->getValue('storage_method'));
    $config->save();
    return parent::submitForm($form, $form_state);
  }

}
