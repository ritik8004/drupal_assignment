<?php

namespace Drupal\alshaya_click_collect\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class StoresFinderConfigForm.
 */
class ClickCollectConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'alshaya_click_collect_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return ['alshaya_click_collect.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $config = $this->config('alshaya_click_collect.settings');

    $form['feature_status'] = [
      '#type' => 'select',
      '#title' => $this->t('Feature status'),
      '#required' => TRUE,
      '#options' => [
        'enabled' => $this->t('enabled'),
        'disabled' => $this->t('disabled'),
      ],
      '#default_value' => $config->get('feature_status') ?? 'enabled',
    ];

    $form['checkout_click_collect_available'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Checkout: Click and Collect available'),
      '#required' => TRUE,
      '#default_value' => $config->get('checkout_click_collect_available'),
    ];

    $form['checkout_click_collect_unavailable'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Checkout: Click and Collect unavailable'),
      '#required' => TRUE,
      '#default_value' => $config->get('checkout_click_collect_unavailable'),
    ];

    $form['click_collect_rnc'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Click and Collect Reserve and collect short description'),
      '#required' => TRUE,
      '#default_value' => $config->get('click_collect_rnc'),
    ];

    $form['pdp_click_collect_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('PDP: Click and Collect title'),
      '#required' => TRUE,
      '#default_value' => $config->get('pdp_click_collect_title'),
    ];

    $form['pdp_click_collect_subtitle'] = [
      '#type' => 'textfield',
      '#title' => $this->t('PDP: Click and Collect sub-title'),
      '#required' => TRUE,
      '#default_value' => $config->get('pdp_click_collect_subtitle'),
    ];

    $form['pdp_click_collect_unavailable'] = [
      '#type' => 'textfield',
      '#title' => $this->t('PDP: Click and Collect unavailable'),
      '#required' => TRUE,
      '#default_value' => $config->get('pdp_click_collect_unavailable'),
    ];

    $form['pdp_click_collect_price'] = [
      '#type' => 'textfield',
      '#title' => $this->t('PDP: Click and Collect price'),
      '#description' => $this->t('Leave blank for free'),
      '#required' => FALSE,
      '#default_value' => $config->get('pdp_click_collect_price'),
    ];

    $form['pdp_click_collect_help_text'] = [
      '#type' => 'text_format',
      '#format' => 'rich_text',
      '#title' => $this->t('PDP: Click and Collect help text'),
      '#default_value' => $config->get('pdp_click_collect_help_text.value'),
    ];

    $form['pdp_click_collect_select_option_text'] = [
      '#type' => 'text_format',
      '#format' => 'rich_text',
      '#title' => $this->t('PDP: Click and Collect select option text'),
      '#required' => TRUE,
      '#default_value' => $config->get('pdp_click_collect_select_option_text.value'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('alshaya_click_collect.settings');
    $config->set('feature_status', $form_state->getValue('feature_status'));
    $config->set('checkout_click_collect_available', $form_state->getValue('checkout_click_collect_available'));
    $config->set('checkout_click_collect_unavailable', $form_state->getValue('checkout_click_collect_unavailable'));
    $config->set('click_collect_rnc', $form_state->getValue('click_collect_rnc'));
    $config->set('pdp_click_collect_title', $form_state->getValue('pdp_click_collect_title'));
    $config->set('pdp_click_collect_subtitle', $form_state->getValue('pdp_click_collect_subtitle'));
    $config->set('pdp_click_collect_unavailable', $form_state->getValue('pdp_click_collect_unavailable'));
    $config->set('pdp_click_collect_price', $form_state->getValue('pdp_click_collect_price'));
    $config->set('pdp_click_collect_help_text', $form_state->getValue('pdp_click_collect_help_text'));
    $config->set('pdp_click_collect_select_option_text', $form_state->getValue('pdp_click_collect_select_option_text'));
    $config->save();

    // Invalidate the cache tag.
    $tags = ['click-collect-cache-tag'];
    Cache::invalidateTags($tags);

    parent::submitForm($form, $form_state);
  }

}
