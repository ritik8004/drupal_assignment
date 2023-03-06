<?php

namespace Drupal\alshaya_spc\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Alshaya Show Sidebar on Listing pages Configuration form.
 */
class AlshayaShowHideSidebarConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'alshaya_show_hide_sidebar_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['alshaya_spc.show_listing_sidebar'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $show_listing_sidebar_config = $this->config('alshaya_spc.show_listing_sidebar');

    $form['alshaya_show_hide_sidebar_settings']['show_plp_sidebar'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable Sidebar for PLP pages'),
      '#description' => $this->t('Sidebar will be enabled for PLP pages if checkbox is checked.'),
      '#default_value' => $show_listing_sidebar_config->get('show_plp_sidebar'),
    ];

    $form['alshaya_show_hide_sidebar_settings']['show_srp_sidebar'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable Sidebar for SRP pages'),
      '#description' => $this->t('Sidebar will be enabled for SRP pages if checkbox is checked.'),
      '#default_value' => $show_listing_sidebar_config->get('show_srp_sidebar'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('alshaya_spc.show_listing_sidebar')
      ->set('show_plp_sidebar', $form_state->getValue('show_plp_sidebar'))
      ->set('show_srp_sidebar', $form_state->getValue('show_srp_sidebar'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
