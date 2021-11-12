<?php

namespace Drupal\alshaya_wishlist\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Alshaya Wishlist configuration form.
 */
class AlshayaWishlistConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'alshaya_wishlist_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['alshaya_wishlist.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['alshaya_wishlist']['wishlist_enabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable Wishlist'),
      '#description' => $this->t('Switch to enable or disable Wishlist feature.'),
      '#default_value' => $this->config('alshaya_wishlist.settings')->get('wishlist_enabled'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('alshaya_wishlist.settings')
      ->set('wishlist_enabled', $form_state->getValue('wishlist_enabled'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
