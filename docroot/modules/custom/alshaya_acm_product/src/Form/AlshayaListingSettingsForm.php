<?php

namespace Drupal\alshaya_acm_product\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class AlshayaListingSettingsForm.
 */
class AlshayaListingSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'alshaya_acm_listing_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return ['alshaya_acm_product.listing_settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('alshaya_acm_product.listing_settings');
    $config->set('filter_oos_product', $form_state->getValue('filter_oos_product'));
    $config->save();
    return parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $config = $this->config('alshaya_acm_product.listing_settings');

    $form['filter_oos_product'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Display out of stock products in listing pages.'),
      '#default_value' => $config->get('filter_oos_product'),
    ];

    return $form;
  }

}
