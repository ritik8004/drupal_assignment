<?php

namespace Drupal\alshaya_user\Form;

use Drupal\alshaya_user\Plugin\Block\MyAccountLinks;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class CartConfigForm.
 */
class UserConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'alshaya_user_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return ['alshaya_user.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('alshaya_user.settings');
    $config->set('terms_conditions', $form_state->getValue('terms_conditions'));
    $config->set('user_register_complete', $form_state->getValue('user_register_complete'));
    $config->set('password_tooltip', $form_state->getValue('password_tooltip'));
    $config->set('password_tooltip_change_pwd', $form_state->getValue('password_tooltip_change_pwd'));
    $config->set('my_account_enabled_links', serialize($form_state->getValue('my_account_enabled_links')));
    $config->save();

    return parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $config = $this->config('alshaya_user.settings');

    $form['terms_conditions'] = [
      '#type' => 'text_format',
      '#format' => 'rich_text',
      '#title' => $this->t('Terms and Conditions label'),
      '#required' => TRUE,
      '#default_value' => $config->get('terms_conditions.value'),
    ];

    $form['user_register_complete'] = [
      '#type' => 'text_format',
      '#format' => 'rich_text',
      '#description' => $this->t('Use [email] in the text where you want to show the dynamic email address.'),
      '#title' => $this->t('User registration completion message'),
      '#required' => TRUE,
      '#default_value' => $config->get('user_register_complete.value'),
    ];

    $form['password_tooltip'] = [
      '#type' => 'text_format',
      '#format' => 'rich_text',
      '#title' => $this->t('Password tooltip'),
      '#required' => TRUE,
      '#default_value' => $config->get('password_tooltip.value'),
    ];

    $form['password_tooltip_change_pwd'] = [
      '#type' => 'text_format',
      '#format' => 'rich_text',
      '#title' => $this->t('Password tooltip for Change Password'),
      '#required' => TRUE,
      '#default_value' => $config->get('password_tooltip_change_pwd.value'),
    ];

    $my_account_link_options = [];
    $my_account_link_default_value = [];

    if ($my_account_enabled_links = $config->get('my_account_enabled_links')) {
      // phpcs:ignore
      $my_account_enabled_links = unserialize($my_account_enabled_links);
    }

    $my_account_links = MyAccountLinks::getMyAccountLinks();

    foreach ($my_account_links as $key => $link) {
      $my_account_link_options[$key] = $link['text'];

      if (empty($my_account_enabled_links)) {
        $my_account_link_default_value[] = $key;
      }
      elseif ($my_account_enabled_links[$key]) {
        $my_account_link_default_value[] = $key;
      }
    }

    $form['my_account_enabled_links'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('My Account Enabled Links'),
      '#required' => TRUE,
      '#default_value' => $my_account_link_default_value,
      '#options' => $my_account_link_options,
    ];

    return $form;
  }

}
