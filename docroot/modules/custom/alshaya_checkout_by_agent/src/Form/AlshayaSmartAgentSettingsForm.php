<?php

namespace Drupal\alshaya_checkout_by_agent\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\ConfigFormBase;

/**
 * Class Alshaya Smart Agent settings.
 *
 * @package Drupal\alshaya_checkout_by_agent\Form
 */
class AlshayaSmartAgentSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'alshaya_smart_agent_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['alshaya_checkout_by_agent.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $config = $this->config('alshaya_checkout_by_agent.settings');
    $user_agents = $config->get('smart_user_agents') ?? [];
    $form['smart_user_agents'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Browser User Agents'),
      '#description' => $this->t('Separate browser user agents in a newline. Eg.: <br/> AlshayaSmartAgentDevice <br/> iPad'),
      '#default_value' => implode(PHP_EOL, $user_agents),
    ];

    $form['smart_agent_ips'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Agent IPs'),
      '#description' => $this->t('Enter IP Address Ranges in CIDR Notation seperated with semi-colons, <b><u>with no trailing semi-colon</u></b>.<br /> Eg. 10.20.30.0/24;192.168.199.1/32;1.0.0.0/8<br />For more information on CIDR notation click <a href="http://www.brassy.net/2007/mar/cidr_basic_subnetting">here</a>.'),
      '#default_value' => $config->get('smart_agent_ips'),
    ];

    $form['whatsapp_template'] = [
      '#type' => 'textfield',
      '#title' => $this->t('WhatsApp Template'),
      '#description' => $this->t('Enter the template name to use for WhatsApp.'),
      '#default_value' => $config->get('whatsapp_template'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('alshaya_checkout_by_agent.settings');
    $smart_user_agents = [];
    if (!empty($form_state->getValue('smart_user_agents'))) {
      $smart_user_agents = preg_split('/\n|\r\n?/', $form_state->getValue('smart_user_agents'));
    }
    $config->set('smart_user_agents', $smart_user_agents);
    $config->set('smart_agent_ips', $form_state->getValue('smart_agent_ips'));
    $config->set('whatsapp_template', $form_state->getValue('whatsapp_template'));
    $config->save();
    parent::submitForm($form, $form_state);
  }

}
