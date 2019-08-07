<?php

namespace Drupal\alshaya_rum\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure Alshaya Real User Monitoring settings for this site.
 */
class AlshayaRumConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'alshaya_rum_config_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'alshaya_rum.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $alshaya_rum_config = $this->config('alshaya_rum.settings');

    $form['rum_user_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Alshaya RUM user id'),
      '#description' => $this->t("Add RUM user id to track usage from <a href='https://www.monitis.com/'>https://www.monitis.com/</a>"),
      '#default_value' => $alshaya_rum_config->get('rum_user_id'),
    ];

    $form['whitelisted_rum_paths'] = [
      '#type' => 'textarea',
      '#title' => $this->t('List of pages on which RUM script should load:'),
      '#default_value' => $alshaya_rum_config->get('whitelisted_rum_paths'),
      '#description' => $this->t("List of pages you want to load RUM script for & measure usage on. Enter relative urls with languagecode e.g., /en/victorias-secret/shop-bras/lining-levels/unlined/ (one per line for mulitple pages). </br> Load on all page by adding `*` to the configuration. Use `&lt;front&gt;` for loading it on Homepage."),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('alshaya_rum.settings')
      ->set('rum_user_id', $form_state->getValue('rum_user_id'))
      ->set('whitelisted_rum_paths', $form_state->getValue('whitelisted_rum_paths'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
