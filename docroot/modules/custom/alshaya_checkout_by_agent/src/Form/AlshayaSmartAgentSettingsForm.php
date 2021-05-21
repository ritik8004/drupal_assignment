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
   * Get Config name.
   *
   * @inheritDoc
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
      '#title' => $this->t('User Agents'),
      '#description' => $this->t('Separate user agents with a newline.'),
      '#default_value' => implode(PHP_EOL, $user_agents),
    ];
    $agent_ips = $config->get('smart_agent_ips') ?? [];
    $form['smart_agent_ips'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Agent IPs'),
      '#description' => $this->t('Separate IPs with a newline.'),
      '#default_value' => implode(PHP_EOL, $agent_ips),
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
    $smart_agent_ips = [];
    if (!empty($form_state->getValue('smart_agent_ips'))) {
      $smart_agent_ips = preg_split('/\n|\r\n?/', $form_state->getValue('smart_agent_ips'));
    }
    $config->set('smart_agent_ips', $smart_agent_ips);
    $config->save();
    parent::submitForm($form, $form_state);
  }

}
